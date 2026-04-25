<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UbigeoDepartamento;
use App\Models\UbigeoDistrito;
use App\Models\UbigeoProvincia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Siembra las tablas de ubigeo desde eApi Perú.
 * Se ejecuta UNA SOLA VEZ al instalar el sistema.
 * Los dropdowns consultan la BD local en runtime — sin latencia ni límites.
 */
class UbigeoSeeder extends Seeder
{
    public function run(): void
    {
        // Evitar duplicar datos si ya existe
        if (UbigeoDepartamento::count() > 0) {
            $this->command->info('Las tablas de ubigeo ya tienen datos. Omitiendo...');
            return;
        }

        $this->command->info('Descargando ubigeos desde eApi Perú...');

        try {
            $response = Http::timeout(30)->get('https://free.e-api.net.pe/ubigeos.json');

            if (!$response->successful()) {
                $this->command->error('No se pudo descargar el archivo de ubigeos.');
                Log::error('UbigeoSeeder: Error al descargar ubigeos', [
                    'status' => $response->status(),
                ]);
                return;
            }

            $ubigeos = $response->json();
            $this->command->info('Archivo descargado. Procesando ubigeos...');

            $departamentos = 0;
            $provincias = 0;
            $distritos = 0;

            foreach ($ubigeos as $nombreDepto => $provinciasArray) {
                // Para obtener el código del departamento necesitamos buscar el primer distrito
                $primerDistritoDepto = collect($provinciasArray)->first() ? collect(collect($provinciasArray)->first())->first() : null;
                $codDepto = $primerDistritoDepto ? substr((string) $primerDistritoDepto['ubigeo'], 0, 2) : str_pad((string)($departamentos + 1), 2, '0', STR_PAD_LEFT);

                // Crear departamento
                $departamentoModel = UbigeoDepartamento::create([
                    'nombre' => $this->normalizarNombre($nombreDepto),
                    'codigo' => $codDepto,
                ]);
                $departamentos++;

                // Iterar provincias
                foreach ($provinciasArray as $nombreProv => $distritosArray) {
                    $primerDistritoProv = collect($distritosArray)->first();
                    $codProv = $primerDistritoProv ? substr((string) $primerDistritoProv['ubigeo'], 0, 4) : $codDepto . str_pad((string)($provincias + 1), 2, '0', STR_PAD_LEFT);

                    $provinciaModel = UbigeoProvincia::create([
                        'departamento_id' => $departamentoModel->id,
                        'nombre' => $this->normalizarNombre($nombreProv),
                        'codigo' => $codProv,
                    ]);
                    $provincias++;

                    // Iterar distritos
                    foreach ($distritosArray as $nombreDist => $distData) {
                        $codigoUbigeo = str_pad((string) ($distData['ubigeo'] ?? ''), 6, '0', STR_PAD_LEFT);

                        UbigeoDistrito::create([
                            'provincia_id' => $provinciaModel->id,
                            'nombre' => $this->normalizarNombre($nombreDist),
                            'codigo' => substr($codigoUbigeo, 0, 6),
                            'codigo_ubigeo' => $codigoUbigeo,
                        ]);
                        $distritos++;
                    }
                }
            }

            $this->command->info("Ubigeo sembrado: {$departamentos} departamentos, {$provincias} provincias, {$distritos} distritos.");
        } catch (\Exception $e) {
            $this->command->error("Error al procesar ubigeos: {$e->getMessage()}");
            Log::error('UbigeoSeeder: Excepción', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Normaliza nombres a Title Case para consistencia visual.
     */
    private function normalizarNombre(string $nombre): string
    {
        return mb_convert_case(mb_strtolower(trim($nombre)), MB_CASE_TITLE, 'UTF-8');
    }
}
