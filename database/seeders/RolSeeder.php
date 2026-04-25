<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Siembra los roles y permisos base del sistema.
 * 5 roles definidos por la especificación:
 *   super_admin, administrador, veterinario, recepcionista, auxiliar_veterinario
 */
class RolSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permisos del sistema ──
        $permisos = [
            // Clientes
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',
            'clientes.importar',

            // Mascotas
            'mascotas.ver',
            'mascotas.crear',
            'mascotas.editar',
            'mascotas.eliminar',

            // Citas
            'citas.ver',
            'citas.crear',
            'citas.editar',
            'citas.cancelar',

            // Historia clínica
            'historias.ver',
            'historias.crear',
            'historias.editar',

            // Vacunas
            'vacunas.ver',
            'vacunas.aplicar',

            // Inventario
            'inventario.ver',
            'inventario.gestionar',
            'inventario.ajustar',

            // Facturación
            'facturacion.ver',
            'facturacion.emitir',
            'facturacion.anular',

            // Caja
            'caja.ver',
            'caja.abrir',
            'caja.cerrar',

            // Hospitalización
            'hospitalizacion.ver',
            'hospitalizacion.gestionar',

            // Reportes
            'reportes.ver',
            'reportes.exportar',

            // Configuración
            'configuracion.ver',
            'configuracion.editar',

            // Usuarios
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',

            // Recordatorios
            'recordatorios.ver',
            'recordatorios.gestionar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // ── Roles y sus permisos ──

        // Super Admin: acceso total
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // Administrador: todo excepto configuración avanzada
        $admin = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $admin->givePermissionTo(array_filter($permisos, fn ($p) => !str_starts_with($p, 'configuracion.')));

        // Veterinario: clínica + consultas + historias + inventario (ver)
        $veterinario = Role::firstOrCreate(['name' => 'veterinario', 'guard_name' => 'web']);
        $veterinario->givePermissionTo([
            'clientes.ver', 'clientes.crear', 'clientes.editar',
            'mascotas.ver', 'mascotas.crear', 'mascotas.editar',
            'citas.ver', 'citas.crear', 'citas.editar',
            'historias.ver', 'historias.crear', 'historias.editar',
            'vacunas.ver', 'vacunas.aplicar',
            'inventario.ver',
            'hospitalizacion.ver', 'hospitalizacion.gestionar',
            'recordatorios.ver',
            'reportes.ver',
        ]);

        // Recepcionista: clientes + citas + facturación + caja
        $recepcionista = Role::firstOrCreate(['name' => 'recepcionista', 'guard_name' => 'web']);
        $recepcionista->givePermissionTo([
            'clientes.ver', 'clientes.crear', 'clientes.editar',
            'mascotas.ver', 'mascotas.crear',
            'citas.ver', 'citas.crear', 'citas.editar', 'citas.cancelar',
            'facturacion.ver', 'facturacion.emitir',
            'caja.ver', 'caja.abrir', 'caja.cerrar',
            'recordatorios.ver',
        ]);

        // Auxiliar veterinario: asistencia clínica limitada
        $auxiliar = Role::firstOrCreate(['name' => 'auxiliar_veterinario', 'guard_name' => 'web']);
        $auxiliar->givePermissionTo([
            'clientes.ver',
            'mascotas.ver',
            'citas.ver',
            'historias.ver',
            'vacunas.ver', 'vacunas.aplicar',
            'inventario.ver',
            'hospitalizacion.ver', 'hospitalizacion.gestionar',
        ]);

        $this->command->info('Roles y permisos sembrados correctamente.');
    }
}
