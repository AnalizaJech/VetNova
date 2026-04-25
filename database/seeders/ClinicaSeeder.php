<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Clinica;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Siembra datos iniciales para el primer uso del sistema.
 * Crea la clínica demo, sucursal principal y usuario super_admin.
 */
class ClinicaSeeder extends Seeder
{
    public function run(): void
    {
        // Crear clínica demo
        $clinica = Clinica::firstOrCreate(
            ['ruc' => '20123456789'],
            [
                'nombre' => 'VetNova Clínica Demo',
                'razon_social' => 'VetNova S.A.C.',
                'direccion' => 'Av. Ejemplo 123, Lima',
                'telefono' => '01-234-5678',
                'email' => 'admin@vetnova.pe',
                'activo' => true,
            ]
        );

        // Crear sucursal principal
        $sucursal = Sucursal::firstOrCreate(
            ['clinica_id' => $clinica->id, 'principal' => true],
            [
                'nombre' => 'Sede Principal',
                'direccion' => 'Av. Ejemplo 123, Lima',
                'telefono' => '01-234-5678',
                'activo' => true,
            ]
        );

        // Crear usuario super_admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@vetnova.pe'],
            [
                'name' => 'Administrador VetNova',
                'password' => Hash::make('password'),
                'clinica_id' => $clinica->id,
                'sucursal_id' => $sucursal->id,
                'activo' => true,
            ]
        );

        $admin->assignRole('super_admin');

        $this->command->info("Clínica demo creada. Login: admin@vetnova.pe / password");
    }
}
