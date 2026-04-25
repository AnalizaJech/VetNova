<div>
    {{-- Header de la vista con título y botones de acción --}}
    <x-header title="Clientes" subtitle="Gestión de propietarios y facturación" separator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Buscar por nombre o DNI/RUC..." wire:model.live.debounce.500ms="search" clearable />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-plus" class="btn-primary" wire:click="create" label="Nuevo Cliente" responsive />
        </x-slot:actions>
    </x-header>

    {{-- Tabla principal --}}
    <x-card class="shadow-sm">
        <x-table :headers="$headers" :rows="$clientes" with-pagination>
            
            {{-- Columna Documento --}}
            @scope('cell_documento', $cliente)
                <div class="font-medium">{{ $cliente->numero_documento }}</div>
                <div class="text-xs text-base-content/60">{{ $cliente->tipo_documento }}</div>
            @endscope

            {{-- Columna Cliente --}}
            @scope('cell_nombres', $cliente)
                <div class="font-semibold text-base-content">{{ $cliente->nombre_completo }}</div>
                @if($cliente->distrito_id)
                    <div class="text-xs text-base-content/60 flex items-center gap-1 mt-1">
                        <x-icon name="o-map-pin" class="w-3 h-3" />
                        {{ $cliente->distrito->nombre }}
                    </div>
                @endif
            @endscope

            {{-- Columna Contacto --}}
            @scope('cell_contacto', $cliente)
                @if($cliente->telefono)
                    <div class="flex items-center gap-1 text-sm">
                        <x-icon name="o-phone" class="w-3 h-3 text-base-content/60" />
                        {{ $cliente->telefono }}
                    </div>
                @endif
                @if($cliente->email)
                    <div class="flex items-center gap-1 text-sm">
                        <x-icon name="o-envelope" class="w-3 h-3 text-base-content/60" />
                        {{ $cliente->email }}
                    </div>
                @endif
            @endscope

            {{-- Columna Activo --}}
            @scope('cell_activo', $cliente)
                @if($cliente->activo)
                    <x-badge value="Activo" class="badge-success badge-sm" />
                @else
                    <x-badge value="Inactivo" class="badge-error badge-sm" />
                @endif
            @endscope

            {{-- Acciones --}}
            @scope('actions', $cliente)
                <div class="flex items-center gap-1">
                    <x-button icon="o-pencil" wire:click="edit({{ $cliente->id }})" class="btn-ghost btn-sm text-info" tooltip="Editar" />
                    
                    <x-button icon="o-trash" class="btn-ghost btn-sm text-error" tooltip="Eliminar"
                        wire:confirm="¿Estás seguro de eliminar a {{ $cliente->nombre_completo }}? Esta acción no se puede deshacer."
                        wire:click="delete({{ $cliente->id }})" />
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- Modal de Crear / Editar --}}
    <x-modal wire:model="modalModal" :title="$isEditing ? 'Editar Cliente' : 'Nuevo Cliente'" separator class="backdrop-blur-sm">
        
        <x-form wire:submit="save">
            
            {{-- Documento e Integración API --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <x-select label="Tipo Doc." wire:model.live="tipo_documento" :options="$tiposDocumento" />
                </div>
                
                <div class="md:col-span-8 flex items-end gap-2">
                    <div class="flex-1">
                        <x-input label="Número" wire:model="numero_documento" placeholder="Ej. 70123456" />
                    </div>
                    
                    @if(in_array($tipo_documento, ['DNI', 'RUC']))
                        <x-button 
                            icon="o-magnifying-glass" 
                            class="btn-primary mb-0" 
                            wire:click="buscarEnApi" 
                            spinner="buscarEnApi" 
                            tooltip="Buscar en RENIEC/SUNAT"
                        />
                    @endif
                </div>
            </div>

            {{-- Nombres y Apellidos --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="{{ $tipo_documento === 'RUC' ? 'Razón Social' : 'Nombres' }}" wire:model="nombres" />
                
                @if($tipo_documento !== 'RUC')
                    <x-input label="Apellidos" wire:model="apellidos" />
                @endif
            </div>

            {{-- Contacto --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="Teléfono / Celular" wire:model="telefono" icon="o-phone" />
                <x-input label="Correo Electrónico" wire:model="email" type="email" icon="o-envelope" />
            </div>

            <x-hr />
            
            {{-- Ubicación --}}
            <h3 class="text-sm font-semibold text-base-content/70">Ubicación (Opcional)</h3>
            
            {{-- Componente Ubigeo Reutilizable --}}
            @if($modalModal)
                <livewire:components.ubigeo-selector :distrito_id="$distrito_id" wire:key="ubigeo-{{ $cliente_id ?? 'new' }}" />
            @endif

            <x-input label="Dirección Exacta" wire:model="direccion" icon="o-map" placeholder="Av. Los Pinos 123..." />

            <x-hr />

            {{-- Opciones Adicionales --}}
            <x-textarea label="Notas / Alertas" wire:model="notas" placeholder="Ej: Cliente problemático, siempre paga en efectivo..." rows="2" />
            
            <x-toggle label="Cliente Activo" wire:model="activo" class="toggle-success" />

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.modalModal = false" class="btn-ghost" />
                <x-button label="Guardar" type="submit" class="btn-primary" icon="o-check" spinner="save" />
            </x-slot:actions>
        </x-form>

    </x-modal>
</div>
