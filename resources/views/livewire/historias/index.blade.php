<div>
    {{-- Header --}}
    <x-header title="Historias Clínicas" subtitle="Registro de consultas médicas" separator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Buscar paciente o motivo..." wire:model.live.debounce.500ms="search" clearable class="w-full md:w-64" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-plus" class="btn-primary" wire:click="create" label="Nueva Consulta" responsive />
        </x-slot:actions>
    </x-header>

    {{-- Tabla principal --}}
    <x-card class="shadow-sm">
        <x-table :headers="$headers" :rows="$historias" with-pagination>
            
            {{-- Columna Fecha --}}
            @scope('cell_fecha', $historia)
                <div class="font-bold text-base-content">{{ $historia->fecha->translatedFormat('d M Y') }}</div>
                <div class="text-xs text-base-content/60">{{ $historia->fecha->format('h:i A') }}</div>
            @endscope

            {{-- Columna Paciente --}}
            @scope('cell_paciente', $historia)
                <div class="font-semibold text-primary">{{ $historia->mascota->nombre ?? 'N/A' }}</div>
                <div class="text-xs text-base-content/60 mt-1">
                    Dueño: {{ $historia->mascota->cliente->nombres ?? 'N/A' }}
                </div>
            @endscope

            {{-- Columna Motivo / Triage --}}
            @scope('cell_motivo', $historia)
                <div class="font-medium text-sm">{{ Str::limit($historia->motivo_consulta, 40) }}</div>
                <div class="flex items-center gap-2 mt-1 text-xs text-base-content/60">
                    @if($historia->peso) <span>⚖️ {{ $historia->peso }} kg</span> @endif
                    @if($historia->temperatura) <span>🌡️ {{ $historia->temperatura }}°C</span> @endif
                </div>
            @endscope

            {{-- Columna Veterinario --}}
            @scope('cell_veterinario', $historia)
                <div class="flex items-center gap-2">
                    <x-icon name="o-user" class="w-4 h-4 text-base-content/50" />
                    <span class="text-sm">{{ $historia->veterinario->name ?? 'N/A' }}</span>
                </div>
            @endscope

            {{-- Acciones --}}
            @scope('actions', $historia)
                <div class="flex items-center gap-1">
                    <x-button icon="o-eye" link="#" class="btn-ghost btn-sm text-base-content" tooltip="Ver completo" />
                    <x-button icon="o-pencil" wire:click="edit({{ $historia->id }})" class="btn-ghost btn-sm text-info" tooltip="Editar" />
                </div>
            @endscope
        </x-table>
        
        @if($historias->isEmpty())
            <div class="text-center py-10 text-base-content/50">
                <x-icon name="o-clipboard-document-list" class="w-12 h-12 mx-auto mb-3 opacity-20" />
                <p>No se encontraron registros médicos.</p>
            </div>
        @endif
    </x-card>

    {{-- Modal de Crear / Editar Consulta --}}
    <x-modal wire:model="modalModal" :title="$isEditing ? 'Editar Consulta' : 'Nueva Consulta Médica'" separator class="backdrop-blur-sm" box-class="max-w-4xl">
        
        <x-form wire:submit="save">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- COLUMNA IZQUIERDA: Paciente y Triage --}}
                <div class="space-y-5">
                    <div class="bg-base-200/50 p-4 rounded-xl border border-base-200">
                        <h3 class="text-sm font-semibold mb-3">Datos del Paciente</h3>
                        
                        <x-choices
                            label="Paciente"
                            wire:model="mascota_id"
                            :options="$mascotasSearch"
                            search-function="buscarMascotas"
                            option-label="nombre"
                            option-sub-label="especie"
                            option-value="id"
                            placeholder="Busca el nombre de la mascota..."
                            searchable
                            single
                            icon="o-heart"
                        />
                        
                        <div class="grid grid-cols-2 gap-3 mt-3">
                            <x-input label="Fecha" wire:model="fecha" type="date" />
                            <x-input label="Hora" wire:model="hora" type="time" />
                        </div>
                    </div>

                    <div class="bg-base-200/50 p-4 rounded-xl border border-base-200">
                        <h3 class="text-sm font-semibold mb-3">Triage (Constantes)</h3>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <x-input label="Peso (kg)" wire:model="peso" type="number" step="0.01" icon="o-scale" hint="Actualizará el perfil" />
                            <x-input label="Temperatura (°C)" wire:model="temperatura" type="number" step="0.1" icon="o-fire" />
                            <x-input label="Frec. Cardíaca (lpm)" wire:model="frecuencia_cardiaca" type="number" />
                            <x-input label="Frec. Resp. (rpm)" wire:model="frecuencia_respiratoria" type="number" />
                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA: Datos Clínicos --}}
                <div class="space-y-4">
                    <x-input label="Motivo de Consulta" wire:model="motivo_consulta" required placeholder="Ej. Diarrea y vómitos desde ayer" />
                    
                    <x-textarea label="Anamnesis y Examen Físico" wire:model="anamnesis" rows="3" placeholder="Detalle los síntomas reportados y los hallazgos clínicos..." />
                    
                    <x-textarea label="Diagnóstico Presuntivo" wire:model="diagnostico_presuntivo" rows="2" placeholder="Ej. Gastroenteritis infecciosa" class="border-warning/50 focus:border-warning" />
                    
                    <x-textarea label="Tratamiento e Indicaciones" wire:model="tratamiento_indicaciones" rows="3" placeholder="Receta médica, dosificación, recomendaciones en casa..." class="border-success/50 focus:border-success" />
                    
                    <x-input label="Próxima cita recomendada" wire:model="proxima_cita_recomendada" type="date" icon="o-calendar" hint="Para revisión o control" />
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.modalModal = false" class="btn-ghost" />
                <x-button label="Guardar Consulta" type="submit" class="btn-primary" icon="o-check" spinner="save" />
            </x-slot:actions>
        </x-form>

    </x-modal>
</div>
