<div>
    <x-header title="Gestión de Usuarios" subtitle="Administra los usuarios y roles de tu clínica" separator>
        <x-slot:actions>
            @can('usuarios.crear')
                <x-button label="Nuevo Usuario" icon="o-plus" class="btn-primary" wire:click="create" />
            @endcan
        </x-slot:actions>
    </x-header>

    {{-- Buscador --}}
    <div class="mb-6">
        <x-input icon="o-magnifying-glass" placeholder="Buscar por nombre, email o DNI..." wire:model.live.debounce.300ms="search" clearable />
    </div>

    {{-- Tabla de usuarios --}}
    <x-card shadow class="border border-base-200">
        <x-table :headers="$headers" :rows="$users" striped @row-click="">
            {{-- Columna Nombre --}}
            @scope('cell_name', $user)
                <div>
                    <p class="font-semibold text-base-content">{{ $user->name }}</p>
                        @if($user->dni)
                            <p class="text-xs text-base-content/50">DNI: {{ $user->dni }}</p>
                        @endif
                    </div>
                </div>
            @endscope

            {{-- Columna Email --}}
            @scope('cell_email', $user)
                <div>
                    <p class="text-sm">{{ $user->email }}</p>
                    @if($user->telefono)
                        <p class="text-xs text-base-content/50">{{ $user->telefono }}</p>
                    @endif
                </div>
            @endscope

            {{-- Columna Rol --}}
            @scope('cell_rol', $user)
                @php
                    $rol = $user->roles->first()?->name ?? 'sin_rol';
                    $badgeClass = match($rol) {
                        'super_admin' => 'badge-error',
                        'administrador' => 'badge-warning',
                        'veterinario' => 'badge-info',
                        'recepcionista' => 'badge-success',
                        'auxiliar_veterinario' => 'badge-ghost',
                        default => 'badge-ghost',
                    };
                    $rolLabel = ucwords(str_replace('_', ' ', $rol));
                @endphp
                <x-badge :value="$rolLabel" class="{{ $badgeClass }} badge-sm" />
            @endscope

            {{-- Columna Estado --}}
            @scope('cell_activo', $user)
                @if($user->activo)
                    <x-badge value="Activo" class="badge-success badge-sm" />
                @else
                    <x-badge value="Inactivo" class="badge-error badge-sm" />
                @endif
            @endscope

            {{-- Acciones --}}
            @scope('actions', $user)
                <div class="flex gap-1">
                    @can('usuarios.editar')
                        <x-button icon="o-pencil-square" class="btn-ghost btn-xs" wire:click="edit({{ $user->id }})" tooltip="Editar" spinner />
                    @endcan
                    @can('usuarios.editar')
                        @if($user->id !== auth()->id())
                            <x-button 
                                icon="{{ $user->activo ? 'o-lock-closed' : 'o-lock-open' }}" 
                                class="btn-ghost btn-xs {{ $user->activo ? 'text-error' : 'text-success' }}" 
                                wire:click="toggleActivo({{ $user->id }})"
                                wire:confirm="{{ $user->activo ? '¿Desactivar este usuario? No podrá iniciar sesión.' : '¿Reactivar este usuario?' }}"
                                tooltip="{{ $user->activo ? 'Desactivar' : 'Activar' }}"
                                spinner />
                        @endif
                    @endcan
                </div>
            @endscope
        </x-table>

        {{-- Paginación --}}
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </x-card>

    {{-- ═══════════ MODAL CREAR / EDITAR ═══════════ --}}
    <x-modal wire:model="modalModal" title="{{ $isEditing ? 'Editar Usuario' : 'Nuevo Usuario' }}" box-class="max-w-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Nombre --}}
            <x-input label="Nombre completo" wire:model="name" icon="o-user" placeholder="Nombre del usuario" error-field="name" />

            {{-- Email --}}
            <x-input label="Correo electrónico" wire:model="email" icon="o-envelope" type="email" placeholder="email@ejemplo.com" error-field="email" />

            {{-- Teléfono --}}
            <x-input label="Teléfono" wire:model="telefono" icon="o-phone" placeholder="987654321" error-field="telefono" />

            {{-- DNI --}}
            <x-input 
                label="DNI / RUC" 
                wire:model.live="dni" 
                icon="o-identification" 
                placeholder="Ingresar DNI o RUC" 
                maxlength="11" 
                error-field="dni"
            >
                <x-slot:append>
                    <x-button 
                        icon="o-magnifying-glass" 
                        class="btn-primary rounded-l-none" 
                        wire:click="buscarDocumento" 
                        spinner="buscarDocumento"
                        tooltip="Buscar en RENIEC/SUNAT"
                    />
                </x-slot:append>
            </x-input>

            {{-- Contraseña --}}
            <x-input label="{{ $isEditing ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña' }}" wire:model="password" type="password" icon="o-key" placeholder="Mínimo 8 caracteres" error-field="password" />

            {{-- Confirmar contraseña --}}
            <x-input label="Confirmar contraseña" wire:model="password_confirmation" type="password" icon="o-key" placeholder="Repetir contraseña" />

            {{-- Rol --}}
            <x-select label="Rol del usuario" wire:model="rol" :options="$rolesDisponibles" placeholder="Seleccionar rol" icon="o-shield-check" error-field="rol" />

            {{-- Activo --}}
            <div class="flex items-center pt-6">
                <x-toggle label="Usuario activo" wire:model="activo" />
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Cancelar" class="btn-ghost" wire:click="$set('modalModal', false)" />
            <x-button label="{{ $isEditing ? 'Actualizar' : 'Crear Usuario' }}" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>
</div>
