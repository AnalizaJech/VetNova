<div>
    {{-- Header y Filtros --}}
    <x-header title="Agenda de Citas" subtitle="Programación de pacientes" separator>
        <x-slot:actions>
            <x-button icon="o-plus" class="btn-primary" wire:click="create" label="Agendar Cita" responsive />
        </x-slot:actions>
    </x-header>

    {{-- Filtros Avanzados --}}
    <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-200 mb-6 flex flex-wrap items-center gap-4">
        <div class="hidden md:flex items-center gap-2">
            <x-icon name="o-funnel" class="w-5 h-5 text-primary/70" />
            <span class="font-bold text-sm">Filtros:</span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1">
            <x-input type="date" wire:model.live="filtroFecha" icon="o-calendar" label="Fecha" class="input-sm" />
            <x-select wire:model.live="filtroEstado" :options="$estados" placeholder="Todos los estados" icon="o-check-circle" label="Estado" class="select-sm" />
            <x-input icon="o-magnifying-glass" placeholder="Buscar paciente o dueño..." wire:model.live.debounce.500ms="search" clearable label="Búsqueda rápida" class="input-sm" />
        </div>
    </div>

    {{-- Stats Rápidos del Día (Se oculta si no filtramos por una fecha exacta) --}}
    @if($filtroFecha)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @php
                $pendientes = $citas->whereIn('estado', ['PENDIENTE', 'CONFIRMADA'])->count();
                $enProgreso = $citas->where('estado', 'EN_PROGRESO')->count();
                $completadas = $citas->where('estado', 'COMPLETADA')->count();
                $noAsistio = $citas->where('estado', 'NO_ASISTIO')->count();
            @endphp
            <div class="bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">
                <p class="text-sm text-base-content/60">Por Atender</p>
                <p class="text-2xl font-bold font-heading text-primary">{{ $pendientes }}</p>
            </div>
            <div class="bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">
                <p class="text-sm text-base-content/60">En Progreso</p>
                <p class="text-2xl font-bold font-heading text-info">{{ $enProgreso }}</p>
            </div>
            <div class="bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">
                <p class="text-sm text-base-content/60">Completadas</p>
                <p class="text-2xl font-bold font-heading text-success">{{ $completadas }}</p>
            </div>
            <div class="bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">
                <p class="text-sm text-base-content/60">No Asistieron</p>
                <p class="text-2xl font-bold font-heading text-error">{{ $noAsistio }}</p>
            </div>
        </div>
        <div class="text-[10px] text-base-content/40 mb-2 px-1">(en esta página)</div>
    @endif

    {{-- Tabla --}}
    <x-card class="shadow-sm">
        <x-table :headers="$headers" :rows="$citas" with-pagination>
            
            {{-- Columna Horario --}}
            @scope('cell_horario', $cita)
                <div class="font-bold text-lg text-primary">{{ $cita->hora_formateada }}</div>
                <div class="text-xs text-base-content/60">{{ $cita->fecha_formateada }}</div>
            @endscope

            {{-- Columna Paciente --}}
            @scope('cell_paciente', $cita)
                <div class="flex items-center gap-3">
                    <a href="{{ route('mascotas.perfil', $cita->mascota_id) }}" class="font-semibold text-primary hover:underline" wire:navigate>
                        {{ $cita->mascota->nombre ?? 'N/A' }}
                    </a>
                </div>
                <div class="text-xs text-base-content/60 mt-1">
                    Dueño: {{ $cita->cliente->nombres ?? 'N/A' }}
                </div>
            @endscope

            {{-- Columna Motivo --}}
            @scope('cell_motivo', $cita)
                <div class="text-sm font-medium">{{ $cita->motivo }}</div>
                <div class="text-xs text-base-content/60 flex items-center gap-1 mt-1">
                    <x-icon name="o-user" class="w-3 h-3" />
                    Vet: {{ $cita->veterinario->name ?? 'No asignado' }}
                </div>
            @endscope

            {{-- Columna Estado --}}
            @scope('cell_estado', $cita)
                @php
                    $color = match($cita->estado) {
                        'PENDIENTE' => 'badge-warning badge-outline',
                        'CONFIRMADA' => 'badge-info',
                        'EN_PROGRESO' => 'badge-primary',
                        'COMPLETADA' => 'badge-success',
                        'CANCELADA' => 'badge-error',
                        'NO_ASISTIO' => 'badge-neutral',
                        default => 'badge-ghost',
                    };
                @endphp
                <x-badge :value="$cita->estado" class="{{ $color }} badge-sm font-semibold" />
                @if($cita->historia_clinica_count > 0)
                    <x-badge icon="o-check-circle" value="Historia OK" class="badge-success badge-sm font-semibold ml-1" />
                @endif
            @endscope

            {{-- Acciones y Cambios de estado rápidos --}}
            @scope('actions', $cita)
                <div class="flex items-center gap-2">
                    
                    @if(in_array($cita->estado, ['PENDIENTE', 'CONFIRMADA', 'EN_PROGRESO']))
                        <x-button
                            icon="o-play"
                            wire:click="iniciarAtencion({{ $cita->id }})"
                            class="btn-success btn-sm"
                            tooltip="Iniciar consulta y crear historia clínica"
                            label="Atender"
                            responsive
                        />
                    @endif
                    {{-- Dropdown de cambio rápido de estado --}}
                    @if(in_array($cita->estado, ['PENDIENTE', 'CONFIRMADA', 'EN_PROGRESO']))
                        <x-dropdown icon="o-chevron-down" class="btn-sm btn-ghost">
                            @if($cita->estado === 'PENDIENTE')
                                <x-menu-item title="Confirmar" icon="o-check" wire:click="cambiarEstado({{ $cita->id }}, 'CONFIRMADA')" />
                            @endif
                            <x-menu-item title="En Progreso" icon="o-play" wire:click="cambiarEstado({{ $cita->id }}, 'EN_PROGRESO')" />
                            <x-menu-item title="Completar" icon="o-check-badge" wire:click="cambiarEstado({{ $cita->id }}, 'COMPLETADA')" class="text-success" />
                            <x-menu-separator />
                            <x-menu-item title="No Asistió" icon="o-x-mark" wire:click="cambiarEstado({{ $cita->id }}, 'NO_ASISTIO')" class="text-error" />
                        </x-dropdown>
                    @endif

                    @if($cita->estado === 'CONFIRMADA')
                        <x-button icon="o-phone" link="tel:{{ $cita->cliente->telefono }}" class="btn-ghost btn-sm text-success" tooltip="Llamar al cliente" />
                    @endif

                    @if($cita->estado === 'COMPLETADA')
                        <x-button icon="o-document-magnifying-glass" link="{{ route('historias', ['search' => $cita->mascota->nombre]) }}" wire:navigate class="btn-ghost btn-sm text-primary" tooltip="Ver Historia" />
                    @endif

                    <x-button icon="o-pencil" wire:click="edit({{ $cita->id }})" class="btn-ghost btn-sm text-info" tooltip="Editar" />
                </div>
            @endscope
        </x-table>

        @if($citas->isEmpty())
            <div class="text-center py-10 text-base-content/50">
                <x-icon name="o-calendar" class="w-12 h-12 mx-auto mb-3 opacity-20" />
                <p>No hay citas programadas para estos filtros.</p>
            </div>
        @endif
    </x-card>

    {{-- Modal de Crear / Editar Cita --}}
    <x-modal wire:model="modalModal" :title="$isEditing ? 'Modificar Cita' : 'Agendar Nueva Cita'" separator class="backdrop-blur-sm">
        
        <x-form wire:submit="save">
            
            {{-- Propietario (Búsqueda Asíncrona) --}}
            <x-choices
                label="Cliente Propietario"
                wire:model.live="cliente_id"
                :options="$clientesSearch"
                search-function="buscarClientes"
                option-label="nombre_completo"
                option-value="id"
                placeholder="Busca por nombre o documento..."
                searchable
                single
                clearable
                icon="o-user"
            />

            {{-- Paciente (Se carga al seleccionar el cliente) --}}
            <x-choices-offline
                label="Paciente (Mascota)"
                wire:model="mascota_id"
                :options="$mascotasSelect"
                option-label="nombre"
                option-sub-label="descripcion_selector"
                option-value="id"
                placeholder="Seleccione la mascota"
                single
                clearable
                icon="o-heart"
                :disabled="empty($mascotasSelect)"
            />

            <x-hr />

            {{-- Fecha y Hora --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="Fecha" wire:model="fecha" type="date" icon="o-calendar" />
                <x-input label="Hora" wire:model="hora" type="time" icon="o-clock" />
            </div>

            {{-- Motivo y Veterinario --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="Motivo de consulta" wire:model="motivo" placeholder="Ej. Vacuna Quintuple" />
                
                <x-choices-offline
                    label="Veterinario Asignado *"
                    wire:model="veterinario_id"
                    :options="$veterinariosSelect"
                    option-label="name"
                    option-value="id"
                    placeholder="Seleccione veterinario..."
                    single
                    clearable
                />
            </div>

            {{-- Estado (Solo al editar) --}}
            @if($isEditing)
                <x-select label="Estado" wire:model="estado" :options="$estados" />
            @endif

            <x-textarea label="Notas / Observaciones" wire:model="notas" rows="2" />

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.modalModal = false" class="btn-ghost" />
                <x-button label="Guardar Cita" type="submit" class="btn-primary" icon="o-check" spinner="save" />
            </x-slot:actions>
        </x-form>

    </x-modal>
</div>
