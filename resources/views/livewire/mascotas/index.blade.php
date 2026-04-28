<div>
    {{-- Header --}}
    <x-header title="Mascotas" subtitle="Registro de pacientes" separator>
        <x-slot:actions>
            <x-button icon="o-plus" class="btn-primary" wire:click="create" label="Nueva Mascota" responsive />
        </x-slot:actions>
    </x-header>

    <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-200 mb-6 flex flex-wrap items-center gap-4">
        <div class="hidden md:flex items-center gap-2">
            <x-icon name="o-funnel" class="w-5 h-5 text-primary/70" />
            <span class="font-bold text-sm">Filtros:</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 flex-1">
            <x-select wire:model.live="filtroEspecie" :options="$especies" placeholder="Todas las especies" icon="o-tag" class="select-sm" />
            <x-select wire:model.live="filtroSexo" :options="$sexos" placeholder="Ambos sexos" icon="o-users" class="select-sm" />
            <x-input icon="o-magnifying-glass" placeholder="Buscar por mascota, dueño o DNI..." wire:model.live.debounce.500ms="search" clearable class="input-sm md:col-span-2" />
        </div>
    </div>

    {{-- Tabla --}}
    <x-card class="shadow-sm">
        <x-table :headers="$headers" :rows="$mascotas" with-pagination>
            
            {{-- Columna Mascota --}}
            @scope('cell_mascota', $mascota)
                <div class="flex items-center gap-3">
                    <div>
                        <a href="{{ route('mascotas.perfil', $mascota) }}" class="font-bold text-primary hover:underline" wire:navigate>
                            {{ $mascota->nombre }}
                        </a>
                        <div class="text-xs text-base-content/60 flex items-center gap-1">
                            @if($mascota->sexo === 'M') <x-icon name="o-user" class="w-3 h-3 text-info inline" /> Macho @else <x-icon name="o-user" class="w-3 h-3 text-pink-500 inline" /> Hembra @endif
                            @if($mascota->edad_readable !== 'Desconocida')
                                &bull; {{ $mascota->edad_readable }}
                            @endif
                        </div>
                    </div>
                </div>
            @endscope

            {{-- Columna Detalles --}}
            @scope('cell_detalles', $mascota)
                <div class="text-sm">
                    <span class="font-medium">{{ $mascota->especie }}</span>
                    @if($mascota->raza)
                        <span class="text-base-content/60">/ {{ $mascota->raza }}</span>
                    @endif
                </div>
                <div class="text-xs text-base-content/60 mt-1">
                    @if($mascota->peso_actual)
                        {{ $mascota->peso_actual }} kg
                    @else
                        Sin peso registrado
                    @endif
                </div>
            @endscope

            {{-- Columna Propietario --}}
            @scope('cell_propietario', $mascota)
                @if($mascota->cliente)
                    <div class="font-medium text-sm">{{ $mascota->cliente->nombre_completo }}</div>
                    <div class="text-xs text-base-content/60 flex items-center gap-1 mt-1">
                        <x-icon name="o-phone" class="w-3 h-3" />
                        {{ $mascota->cliente->telefono ?? 'Sin teléfono' }}
                    </div>
                @else
                    <span class="text-error text-sm">Sin propietario</span>
                @endif
            @endscope

            {{-- Columna Estado --}}
            @scope('cell_estado', $mascota)
                <div class="flex flex-col gap-1">
                    @if($mascota->fallecido)
                        <x-badge value="Fallecido" class="badge-neutral badge-sm" />
                    @else
                        <x-badge value="Vivo" class="badge-success badge-sm" />
                    @endif
                    
                    @if($mascota->esterilizado)
                        <x-badge value="Esterilizado" class="badge-info badge-outline badge-sm" />
                    @endif
                </div>
            @endscope

            {{-- Acciones --}}
            @scope('actions', $mascota)
                <div class="flex items-center gap-1">
                    <x-button icon="o-clipboard-document-list" link="{{ route('historias', ['search' => $mascota->nombre]) }}" wire:navigate class="btn-ghost btn-sm text-primary" tooltip="Historia Clínica" />
                    <x-button icon="o-pencil" wire:click="edit({{ $mascota->id }})" class="btn-ghost btn-sm text-info" tooltip="Editar" />
                    @if(!$mascota->fallecido)
                        <x-button icon="o-no-symbol" class="btn-ghost btn-sm text-error" tooltip="Marcar como fallecido"
                            wire:confirm="¿Marcar a {{ $mascota->nombre }} como fallecido? El historial médico se conservará."
                            wire:click="marcarFallecido({{ $mascota->id }})" />
                    @endif
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- Modal de Crear / Editar --}}
    <x-modal wire:model="modalModal" :title="$isEditing ? 'Editar Mascota' : 'Nueva Mascota'" separator class="backdrop-blur-sm">
        
        <x-form wire:submit="save">
            
            {{-- Propietario (Búsqueda Asíncrona) --}}
            <div wire:key="client-selector-container">
                <x-choices
                    label="Cliente Propietario"
                    wire:model="cliente_id"
                    :options="$clientesSearch"
                    search-function="buscarClientes"
                    option-label="nombre_completo"
                    option-sub-label="numero_documento"
                    option-value="id"
                    placeholder="Busca por nombre o documento..."
                    no-result-text="No se encontraron clientes"
                    searchable
                    single
                    clearable
                    debounce="300ms"
                    icon="o-user"
                    wire:key="client-selector-{{ $mascota_id ?? 'new' }}"
                />
            </div>

            <x-hr />

            {{-- Datos Básicos --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="Nombre de la mascota" wire:model="nombre" placeholder="Ej. Firulais" icon="o-heart" />
                
                <div class="grid grid-cols-2 gap-2">
                    <x-select label="Especie" wire:model="especie" :options="$especies" />
                    <x-select label="Sexo" wire:model="sexo" :options="$sexos" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="Raza (opcional)" wire:model="raza" placeholder="Ej. Golden Retriever" hint="Puedes dejarlo en blanco" />
                <x-input label="Color (opcional)" wire:model="color" placeholder="Ej. Dorado" hint="Puedes dejarlo en blanco" />
            </div>

            {{-- Biometría --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="Fecha de Nacimiento" wire:model="fecha_nacimiento" type="date" />
                <x-input label="Peso actual (kg)" wire:model="peso_actual" type="number" step="0.01" placeholder="0.00" icon="o-scale" />
            </div>

            <x-hr />

            {{-- Estado Médico --}}
            <h3 class="text-sm font-semibold text-base-content/70 mb-2">Estado Médico Inicial</h3>
            
            <div class="flex items-center gap-6 mb-4">
                <x-toggle label="Esterilizado/Castrado" wire:model="esterilizado" class="toggle-info" />
                <x-toggle label="Fallecido" wire:model="fallecido" class="toggle-error" />
            </div>

            <x-textarea label="Notas Médicas Rápidas" wire:model="notas_medicas" placeholder="Alergias, condiciones preexistentes..." rows="2" />

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.modalModal = false" class="btn-ghost" />
                <x-button label="Guardar" type="submit" class="btn-primary" icon="o-check" spinner="save" />
            </x-slot:actions>
        </x-form>

    </x-modal>
</div>
