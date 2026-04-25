<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Venta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NubefactService
{
    private string $url;
    private string $token;

    public function __construct()
    {
        // Toma los valores globales configurados en Fase 1 (config/services.php y .env)
        $this->url = config('services.nubefact.url', '');
        $this->token = config('services.nubefact.token', '');
    }

    /**
     * Envía la venta a Nubefact para emitir Boleta o Factura.
     */
    public function emitir(Venta $venta): array
    {
        if (empty($this->url) || empty($this->token)) {
            return [
                'exito' => false, 
                'error' => 'No se han configurado las credenciales de Nubefact.'
            ];
        }

        if (!in_array($venta->tipo_comprobante, ['BOLETA', 'FACTURA'])) {
            return [
                'exito' => false, 
                'error' => 'El comprobante tipo TICKET no se envía a SUNAT.'
            ];
        }

        // Calcular bases según SUNAT
        $total_gravada = 0;
        $total_igv = 0;
        $total_exonerada = 0;

        $items = [];
        
        foreach ($venta->detalles as $detalle) {
            $es_gravado = $detalle->afecto_igv;
            
            // SUNAT exige separar Valor Unitario (sin IGV) del Precio Unitario (con IGV)
            if ($es_gravado) {
                $valor_unitario = $detalle->precio_unitario / 1.18;
                $igv_item = $detalle->precio_unitario - $valor_unitario;
                
                $total_gravada += ($valor_unitario * $detalle->cantidad);
                $total_igv += ($igv_item * $detalle->cantidad);
                $tipo_igv = 1; // 1 = Gravado - Operación Onerosa
            } else {
                $valor_unitario = $detalle->precio_unitario;
                $igv_item = 0;
                
                $total_exonerada += ($valor_unitario * $detalle->cantidad);
                $tipo_igv = 8; // 8 = Exonerado - Operación Onerosa
            }

            $items[] = [
                "unidad_de_medida" => "NIU", // Bien o Servicio genérico
                "codigo"           => "P" . str_pad((string)$detalle->producto_id, 4, '0', STR_PAD_LEFT),
                "descripcion"      => $detalle->descripcion,
                "cantidad"         => $detalle->cantidad,
                "valor_unitario"   => round($valor_unitario, 2),
                "precio_unitario"  => round($detalle->precio_unitario, 2),
                "descuento"        => "",
                "subtotal"         => round($valor_unitario * $detalle->cantidad, 2),
                "tipo_de_igv"      => $tipo_igv,
                "igv"              => round($igv_item * $detalle->cantidad, 2),
                "total"            => round($detalle->subtotal, 2),
                "anticipo_regularizacion" => "false"
            ];
        }

        // Tipo de documento del cliente (SUNAT)
        // 1 = DNI, 6 = RUC, - = Varios (Público General)
        $cliente_tipo_doc = "1";
        $cliente_numero = "00000000";
        $cliente_nombre = "PÚBLICO GENERAL";

        if ($venta->cliente) {
            $cliente_tipo_doc = $venta->cliente->tipo_documento === 'RUC' ? "6" : "1";
            $cliente_numero = $venta->cliente->numero_documento ?? "00000000";
            $cliente_nombre = $venta->cliente->nombres . ' ' . $venta->cliente->apellidos;
        } elseif ($venta->tipo_comprobante === 'FACTURA') {
            return ['exito' => false, 'error' => 'Una FACTURA exige tener un cliente con RUC.'];
        }

        // Determinar Serie
        $serie = $venta->tipo_comprobante === 'FACTURA' ? "F001" : "B001";
        
        // El número será el correlativo interno por ahora (Idealmente se hace query de MAX()+1)
        $numero_correlativo = $venta->id;

        $payload = [
            "operacion" => "generar_comprobante",
            "tipo_de_comprobante" => $venta->tipo_comprobante === 'FACTURA' ? "1" : "2",
            "serie" => $serie,
            "numero" => (string) $numero_correlativo,
            "sunat_transaction" => "1", // 1 = Venta Interna
            "cliente_tipo_de_documento" => $cliente_tipo_doc,
            "cliente_numero_de_documento" => $cliente_numero,
            "cliente_denominacion" => $cliente_nombre,
            "cliente_direccion" => $venta->cliente->direccion ?? "LIMA",
            "cliente_email" => $venta->cliente->email ?? "",
            "fecha_de_emision" => $venta->created_at->format('d-m-Y'),
            "moneda" => "1", // 1 = Soles
            "porcentaje_de_igv" => "18.00",
            "total_gravada" => round($total_gravada, 2) > 0 ? round($total_gravada, 2) : "",
            "total_exonerada" => round($total_exonerada, 2) > 0 ? round($total_exonerada, 2) : "",
            "total_igv" => round($total_igv, 2),
            "total" => round($venta->total, 2),
            "detraccion" => "false",
            "enviar_automaticamente_a_la_sunat" => "true",
            "enviar_automaticamente_al_cliente" => "false",
            "formato_de_pdf" => "TICKET",
            "items" => $items
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type'  => 'application/json'
            ])->post($this->url, $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['enlace_del_pdf'])) {
                // Actualizar nuestra base de datos con el link validado y Serie
                $venta->update([
                    'nubefact_enlace_pdf' => $result['enlace_del_pdf'],
                    'nubefact_external_id' => $result['external_id'] ?? null,
                    'serie_correlativo' => $serie . '-' . str_pad((string)$numero_correlativo, 6, '0', STR_PAD_LEFT)
                ]);

                return [
                    'exito' => true,
                    'enlace_pdf' => $result['enlace_del_pdf']
                ];
            }

            Log::error('Nubefact Error', ['payload' => $payload, 'response' => $result]);
            return [
                'exito' => false,
                'error' => $result['errors'] ?? 'Error desconocido al comunicar con SUNAT.'
            ];

        } catch (\Exception $e) {
            Log::error('Nubefact Exception', ['msg' => $e->getMessage()]);
            return [
                'exito' => false,
                'error' => 'Excepción de red al contactar con Nubefact: ' . $e->getMessage()
            ];
        }
    }
}
