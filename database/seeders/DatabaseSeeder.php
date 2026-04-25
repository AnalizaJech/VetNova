<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder principal del sistema.
 * Orden de ejecución importa por dependencias entre tablas.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolSeeder::class,       // 1. Roles y permisos (Spatie)
            UbigeoSeeder::class,    // 2. Ubigeo peruano (eApi Perú)
            ClinicaSeeder::class,   // 3. Clínica demo + usuario admin
        ]);
    }
}
