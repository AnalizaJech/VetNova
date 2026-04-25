<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para consultar la API de PeruAPI.
 * Maneja consultas de DNI, RUC y tipo de cambio con cache agresivo.
 *
 * Plan Free: 100 req/día · 1500/mes · 10 rpm · 1 IP
 * Por eso cacheamos todo lo que podamos.
 */
final class PeruApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.peruapi.base_url', 'https://peruapi.com');
        $this->apiKey = config('services.peruapi.key', '');
    }

    /**
     * Consultar datos de una persona por DNI.
     * Cache: 30 días (los datos de DNI no cambian).
     */
    public function consultarDni(string $dni): ?array
    {
        // Validación local: DNI debe ser exactamente 8 dígitos
        if (!preg_match('/^\d{8}$/', $dni)) {
            return null;
        }

        return Cache::remember(
            "peruapi:dni:{$dni}",
            now()->addDays(30),
            fn () => $this->get("/api/dni/{$dni}")
        );
    }

    /**
     * Consultar datos de una empresa por RUC.
     * Cache: 24 horas (datos pueden cambiar: dirección, estado, etc).
     * Valida el dígito verificador localmente ANTES de llamar a la API.
     */
    public function consultarRuc(string $ruc): ?array
    {
        // Validación local: RUC debe ser 11 dígitos
        if (!preg_match('/^\d{11}$/', $ruc)) {
            return null;
        }

        // Validar dígito verificador del RUC antes de gastar crédito API
        if (!$this->validarDigitoVerificadorRuc($ruc)) {
            return null;
        }

        return Cache::remember(
            "peruapi:ruc:{$ruc}",
            now()->addHours(24),
            fn () => $this->get("/api/ruc/{$ruc}")
        );
    }

    /**
     * Obtener tipo de cambio del día (compra/venta USD).
     * Cache: 12 horas.
     */
    public function tipoCambio(): ?array
    {
        return Cache::remember(
            'peruapi:tipo_cambio',
            now()->addHours(12),
            fn () => $this->get('/api/tipo_cambio')
        );
    }

    /**
     * Realiza la petición HTTP a PeruAPI.
     * Timeout: 8 segundos. Reintentos: 3 con backoff exponencial.
     */
    private function get(string $path): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])
                ->timeout(8)
                ->retry(3, 1000, throw: false)
                ->get("{$this->baseUrl}{$path}");

            if ($response->successful()) {
                $data = $response->json();

                // PeruAPI devuelve code 200 cuando encuentra resultados
                if (isset($data['code']) && $data['code'] !== 200) {
                    Log::warning("PeruAPI: respuesta no exitosa para {$path}", $data);
                    return null;
                }

                return $data;
            }

            // 404 no descuenta crédito — cachear igualmente para no reintentar
            if ($response->status() === 404) {
                Log::info("PeruAPI: recurso no encontrado {$path}");
                return null;
            }

            // 429 — rate limit
            if ($response->status() === 429) {
                Log::warning("PeruAPI: rate limit alcanzado para {$path}");
                return null;
            }

            Log::error("PeruAPI: error HTTP {$response->status()} para {$path}");
            return null;
        } catch (\Exception $e) {
            Log::error("PeruAPI: excepción para {$path}", [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Valida el dígito verificador de un RUC peruano.
     * Algoritmo módulo 11 de SUNAT.
     * Evita gastar créditos API con RUCs inválidos.
     */
    private function validarDigitoVerificadorRuc(string $ruc): bool
    {
        // El primer dígito define el tipo de contribuyente
        $primerDigito = (int) $ruc[0];
        if (!in_array($primerDigito, [1, 2], true)) {
            return false;
        }

        // Factores de multiplicación para módulo 11
        $factores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;

        for ($i = 0; $i < 10; $i++) {
            $suma += (int) $ruc[$i] * $factores[$i];
        }

        $resto = $suma % 11;
        $digito = 11 - $resto;

        if ($digito === 10) {
            $digito = 0;
        } elseif ($digito === 11) {
            $digito = 1;
        }

        return $digito === (int) $ruc[10];
    }
}
