<div>
    {{-- Header --}}
    <x-header title="Historias Clínicas" subtitle="Expediente médico electrónico" separator>
        <x-slot:actions>
            <x-button icon="o-plus" class="btn-primary" wire:click="create" label="Nueva Consulta" responsive />
        </x-slot:actions>
    </x-header>

    <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-200 mb-6 flex flex-wrap items-center gap-4">
        <div class="hidden md:flex items-center gap-2">
            <x-icon name="o-funnel" class="w-5 h-5 text-primary/70" />
            <span class="font-bold text-sm">Filtros:</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1">
            <x-input type="date" wire:model.live="filtroFecha" icon="o-calendar" label="Fecha de consulta" class="input-sm" />
            <x-input icon="o-magnifying-glass" placeholder="Buscar por paciente, dueño o motivo..." wire:model.live.debounce.500ms="search" clearable label="Búsqueda rápida" class="input-sm md:col-span-2" />
        </div>
    </div>

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
                <a href="{{ route('mascotas.perfil', $historia->mascota_id) }}" class="font-semibold text-primary hover:underline" wire:navigate>
                    {{ $historia->mascota->nombre ?? 'N/A' }}
                </a>
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
            {{-- Acciones --}}
            @scope('actions', $historia)
                <div class="flex items-center gap-1">
                    <x-button icon="o-eye" wire:click="verCompleto({{ $historia->id }})" class="btn-ghost btn-sm text-base-content" tooltip="Ver completo" />
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
                            option-sub-label="descripcion_selector"
                            option-value="id"
                            placeholder="Busca nombre de mascota o DNI del dueño..."
                            searchable
                            single
                            clearable
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
                            <x-input label="Frec. Cardíaca (lpm)" wire:model="frecuencia_cardiaca" type="number" icon="o-heart" />
                            <x-input label="Frec. Resp. (rpm)" wire:model="frecuencia_respiratoria" type="number" icon="o-variable" />
                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA: Datos Clínicos --}}
                <div class="space-y-4">
                    <x-input label="Motivo de Consulta" wire:model="motivo_consulta" required placeholder="Ej. Diarrea y vómitos desde ayer" autofocus />
                    
                    <x-textarea label="Anamnesis y Examen Físico" wire:model="anamnesis" rows="3" placeholder="Detalle los síntomas reportados y los hallazgos clínicos..." />
                    
                    <x-textarea label="Diagnóstico Presuntivo" wire:model="diagnostico_presuntivo" rows="2" placeholder="Ej. Gastroenteritis infecciosa" class="border-warning/50 focus:border-warning" />
                    
                    <x-textarea label="Tratamiento e Indicaciones" wire:model="tratamiento_indicaciones" rows="3" placeholder="Receta médica, dosificación, recomendaciones en casa..." class="border-success/50 focus:border-success" />
                    
                    {{-- Sección de Prescripciones --}}
                    <div class="bg-warning/5 p-4 rounded-xl border border-warning/20">
                        <h3 class="text-sm font-semibold mb-3 flex items-center gap-2">
                            <x-icon name="o-beaker" class="w-4 h-4 text-warning" />
                            Prescripciones / Receta Médica
                        </h3>
                        
                        {{-- Mini-formulario para agregar líneas --}}
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <x-input label="Medicamento" wire:model="prescripcion_medicamento"
                                placeholder="Ej. Amoxicilina 250mg" />
                            <x-input label="Dosis" wire:model="prescripcion_dosis"
                                placeholder="Ej. 1 tableta" />
                            <x-input label="Frecuencia" wire:model="prescripcion_frecuencia"
                                placeholder="Ej. Cada 8 horas" />
                            <x-input label="Duración" wire:model="prescripcion_duracion"
                                placeholder="Ej. 7 días" />
                        </div>
                        <x-button icon="o-plus" wire:click="agregarPrescripcion"
                            class="btn-warning btn-sm btn-outline" label="Agregar medicamento" />
                        
                        {{-- Lista de prescripciones agregadas --}}
                        @foreach($prescripciones as $i => $p)
                            <div class="flex items-center justify-between bg-base-100 p-2 rounded-lg mt-2 shadow-sm">
                                <div class="text-sm">
                                    <span class="font-semibold">{{ $p['medicamento'] }}</span>
                                    <span class="text-base-content/60"> — {{ $p['dosis'] }}</span>
                                    @if($p['frecuencia']) <span class="text-xs text-base-content/50"> · {{ $p['frecuencia'] }}</span> @endif
                                </div>
                                <x-button icon="o-x-mark" wire:click="quitarPrescripcion({{ $i }})"
                                    class="btn-ghost btn-xs text-error" />
                            </div>
                        @endforeach
                    </div>

                    <x-input label="Próxima cita recomendada" wire:model="proxima_cita_recomendada" type="date" icon="o-calendar" hint="Para revisión o control" />
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.modalModal = false" class="btn-ghost" />
                <x-button label="Guardar Consulta" type="submit" class="btn-primary" icon="o-check" spinner="save" />
            </x-slot:actions>
        </x-form>

    </x-modal>

    {{-- Modal Visor Completo --}}
    {{-- Modal Visor Completo --}}
    <x-modal wire:model="modalVer" title="Expediente Clínico" subtitle="{{ $historiaSeleccionada?->fecha->format('d/m/Y h:i A') }}" separator class="backdrop-blur-sm" box-class="max-w-4xl">
        @if($historiaSeleccionada)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- Columna Info Paciente y Constantes --}}
                <div class="col-span-1 space-y-4">
                    {{-- Paciente --}}
                    <div class="bg-base-200/50 p-4 rounded-xl border border-base-200">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="bg-primary/10 p-2 rounded-lg">
                                <x-icon name="o-heart" class="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg leading-none">{{ $historiaSeleccionada->mascota->nombre }}</h3>
                                <p class="text-xs text-base-content/60">{{ $historiaSeleccionada->mascota->especie }} &bull; {{ $historiaSeleccionada->mascota->sexo === 'M' ? 'Macho' : 'Hembra' }}</p>
                            </div>
                        </div>
                        <div class="text-xs mt-3 text-base-content/70">
                            <strong>Propietario:</strong> {{ $historiaSeleccionada->mascota->cliente->nombre_completo ?? 'N/A' }}
                        </div>
                        <div class="text-xs mt-1 text-base-content/70">
                            <strong>Veterinario:</strong> {{ $historiaSeleccionada->veterinario->name ?? 'N/A' }}
                        </div>
                    </div>

                    {{-- Triage --}}
                    <div class="bg-base-200/50 p-4 rounded-xl border border-base-200">
                        <h4 class="font-bold text-sm mb-3 border-b border-base-300 pb-2">Constantes (Triage)</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between border-b border-base-300/30 pb-1">
                                <span class="text-base-content/60">Peso:</span>
                                <span class="font-semibold">{{ $historiaSeleccionada->peso ? $historiaSeleccionada->peso . ' kg' : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-300/30 pb-1">
                                <span class="text-base-content/60">Temp:</span>
                                <span class="font-semibold">{{ $historiaSeleccionada->temperatura ? $historiaSeleccionada->temperatura . ' °C' : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-300/30 pb-1">
                                <span class="text-base-content/60">F. Cardíaca:</span>
                                <span class="font-semibold">{{ $historiaSeleccionada->frecuencia_cardiaca ? $historiaSeleccionada->frecuencia_cardiaca . ' lpm' : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">F. Resp:</span>
                                <span class="font-semibold">{{ $historiaSeleccionada->frecuencia_respiratoria ? $historiaSeleccionada->frecuencia_respiratoria . ' rpm' : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Columna Detalles Médicos --}}
                <div class="col-span-1 md:col-span-2 space-y-4">
                    
                    <div class="bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm">
                        <h4 class="text-xs font-bold text-base-content/50 uppercase tracking-wider mb-1">Motivo de Consulta</h4>
                        <p class="text-base font-semibold text-primary">{{ $historiaSeleccionada->motivo_consulta }}</p>
                    </div>

                    <div class="bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm">
                        <h4 class="text-xs font-bold text-base-content/50 uppercase tracking-wider mb-2">Anamnesis y Examen Físico</h4>
                        <p class="text-sm whitespace-pre-wrap text-base-content/80">{{ $historiaSeleccionada->anamnesis ?: 'No se registraron detalles adicionales.' }}</p>
                    </div>

                    <div class="bg-base-100 p-4 rounded-xl border border-warning/30 shadow-sm">
                        <h4 class="text-xs font-bold text-warning uppercase tracking-wider mb-2">Diagnóstico Presuntivo</h4>
                        <p class="text-sm whitespace-pre-wrap font-medium">{{ $historiaSeleccionada->diagnostico_presuntivo ?: 'Sin diagnóstico registrado.' }}</p>
                    </div>

                    <div class="bg-base-100 p-4 rounded-xl border border-success/30 shadow-sm">
                        <h4 class="text-xs font-bold text-success uppercase tracking-wider mb-2">Tratamiento e Indicaciones</h4>
                        <p class="text-sm whitespace-pre-wrap mb-4">{{ $historiaSeleccionada->tratamiento_indicaciones ?: 'Sin indicaciones.' }}</p>
                        
                        @if($historiaSeleccionada->prescripciones->isNotEmpty())
                            <div class="space-y-2 pt-2 border-t border-success/10">
                                @foreach($historiaSeleccionada->prescripciones as $p)
                                    <div class="text-sm bg-success/5 p-2 rounded-lg border border-success/10">
                                        <span class="font-bold text-success">{{ $p->medicamento }}</span>
                                        <span class="text-base-content/70">({{ $p->dosis }})</span>
                                        @if($p->frecuencia) <span class="text-xs italic text-base-content/50"> - {{ $p->frecuencia }}</span> @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($historiaSeleccionada->proxima_cita_recomendada)
                        <div class="bg-info/10 text-info p-3 rounded-xl border border-info/20 text-sm flex items-center gap-2">
                            <x-icon name="o-calendar" class="w-5 h-5" />
                            <strong>Próxima cita recomendada:</strong> {{ $historiaSeleccionada->proxima_cita_recomendada->format('d/m/Y') }}
                        </div>
                    @endif

                </div>
            </div>
        @endif

        <x-slot:actions>
            <x-button label="Imprimir" icon="o-printer" class="btn-primary" onclick="window.print()" />
            <x-button label="Cerrar" @click="$wire.modalVer = false" class="btn-neutral" />
        </x-slot:actions>
    </x-modal>
</div>
