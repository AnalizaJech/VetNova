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
    {{-- Contenedor Invisible para Impresión con Iframe --}}
    <div id="print-container" class="hidden"></div>

    @script
    <script>
        window.imprimirFichaClinica = function() {
            const printContent = document.getElementById('historia-detalle-imprimir').innerHTML;
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            
            const doc = iframe.contentWindow.document;
            doc.write('<html><head><title>VetNeoLink - Historia Clínica</title>');
            doc.write('<style>');
            doc.write('@@page { margin: 1.5cm; size: A4; }');
            doc.write('body { font-family: sans-serif; color: #000; background: #fff; line-height: 1.3; font-size: 10pt; }');
            doc.write('.header { border-bottom: 3px solid #000; margin-bottom: 20px; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: flex-end; }');
            doc.write('.header h1 { margin: 0; font-size: 22pt; font-weight: 900; text-transform: uppercase; }');
            doc.write('.section { border: 1px solid #000; padding: 12px; margin-bottom: 15px; }');
            doc.write('.section-title { font-weight: bold; text-transform: uppercase; font-size: 9pt; border-bottom: 1px solid #000; margin-bottom: 8px; }');
            doc.write('.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }');
            doc.write('.triage-row { display: flex; justify-content: space-between; border-bottom: 1px dashed #ccc; }');
            doc.write('.font-bold { font-weight: bold; }');
            doc.write('.whitespace-pre { white-space: pre-wrap; }');
            doc.write('svg, button, .no-print { display: none !important; }');
            doc.write('</style></head><body>');
            
            doc.write('<div class="header"><div><h1>VetNeoLink</h1><div style="font-size:14pt;font-weight:bold;">HISTORIA CLÍNICA OFICIAL</div></div><div style="font-size:8pt;text-align:right;"><b>Fecha:</b> ' + new Date().toLocaleString() + '</div></div>');
            doc.write(printContent);
            doc.write('</body></html>');
            doc.close();

            setTimeout(() => {
                iframe.contentWindow.print();
                document.body.removeChild(iframe);
            }, 500);
        }
    </script>
    @endscript

    {{-- Modal Visor Completo --}}
    <x-modal wire:model="modalVer" title="Expediente Clínico" subtitle="{{ $historiaSeleccionada?->fecha->format('d/m/Y h:i A') }}" separator class="backdrop-blur-sm" box-class="max-w-4xl">
        @if($historiaSeleccionada)
            <div id="historia-detalle-imprimir">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    {{-- Columna Info Paciente y Constantes --}}
                    <div class="col-span-1 space-y-4">
                        {{-- Paciente --}}
                        <div class="section bg-base-200/50 p-4 rounded-xl border border-base-200">
                            <div class="section-title hidden print:block">Datos del Paciente</div>
                            <div class="flex items-center gap-3 mb-2 no-print">
                                <div class="bg-primary/10 p-2 rounded-lg">
                                    <x-icon name="o-heart" class="w-6 h-6 text-primary" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg leading-none">{{ $historiaSeleccionada->mascota->nombre }}</h3>
                                    <p class="text-xs text-base-content/60">{{ $historiaSeleccionada->mascota->especie }}</p>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-sm print:text-base"><span class="font-bold">Mascota:</span> {{ $historiaSeleccionada->mascota->nombre }} ({{ $historiaSeleccionada->mascota->especie }})</div>
                                <div class="text-sm print:text-base"><span class="font-bold">Propietario:</span> {{ $historiaSeleccionada->mascota->cliente->nombre_completo ?? 'N/A' }}</div>
                                <div class="text-sm print:text-base"><span class="font-bold">Veterinario:</span> {{ $historiaSeleccionada->veterinario->name ?? 'N/A' }}</div>
                            </div>
                        </div>

                        {{-- Triage --}}
                        <div class="section bg-base-200/50 p-4 rounded-xl border border-base-200">
                            <div class="section-title">Constantes Vitales (Triage)</div>
                            <div class="space-y-1 text-sm">
                                <div class="triage-row flex justify-between">
                                    <span>Peso:</span>
                                    <span class="font-bold">{{ $historiaSeleccionada->peso ? $historiaSeleccionada->peso . ' kg' : 'N/A' }}</span>
                                </div>
                                <div class="triage-row flex justify-between">
                                    <span>Temperatura:</span>
                                    <span class="font-bold">{{ $historiaSeleccionada->temperatura ? $historiaSeleccionada->temperatura . ' °C' : 'N/A' }}</span>
                                </div>
                                <div class="triage-row flex justify-between">
                                    <span>F. Cardíaca:</span>
                                    <span class="font-bold">{{ $historiaSeleccionada->frecuencia_cardiaca ? $historiaSeleccionada->frecuencia_cardiaca . ' lpm' : 'N/A' }}</span>
                                </div>
                                <div class="triage-row flex justify-between">
                                    <span>F. Respiratoria:</span>
                                    <span class="font-bold">{{ $historiaSeleccionada->frecuencia_respiratoria ? $historiaSeleccionada->frecuencia_respiratoria . ' rpm' : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Columna Detalles Médicos --}}
                    <div class="col-span-1 md:col-span-2 space-y-4">
                        
                        <div class="section bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm">
                            <div class="section-title">Motivo de Consulta</div>
                            <p class="text-base font-bold text-primary print:text-black">{{ $historiaSeleccionada->motivo_consulta }}</p>
                        </div>

                        <div class="section bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm">
                            <div class="section-title">Anamnesis y Examen Físico</div>
                            <p class="whitespace-pre text-sm text-base-content/80 print:text-black">{{ $historiaSeleccionada->anamnesis ?: 'No registrado.' }}</p>
                        </div>

                        <div class="section bg-base-100 p-4 rounded-xl border border-warning/30 shadow-sm">
                            <div class="section-title">Diagnóstico Presuntivo</div>
                            <p class="whitespace-pre text-sm font-bold">{{ $historiaSeleccionada->diagnostico_presuntivo ?: 'Sin diagnóstico.' }}</p>
                        </div>

                        <div class="section bg-base-100 p-4 rounded-xl border border-success/30 shadow-sm">
                            <div class="section-title">Tratamiento e Indicaciones</div>
                            <p class="whitespace-pre text-sm mb-2">{{ $historiaSeleccionada->tratamiento_indicaciones ?: 'Sin indicaciones.' }}</p>
                            
                            @if($historiaSeleccionada->prescripciones->isNotEmpty())
                                <div class="mt-2 pt-2 border-t border-black/10">
                                    <div class="font-bold text-xs uppercase mb-1">Medicamentos:</div>
                                    @foreach($historiaSeleccionada->prescripciones as $p)
                                        <div class="text-sm">
                                            • <span class="font-bold">{{ $p->medicamento }}</span> 
                                            <span class="opacity-70">({{ $p->dosis }})</span>
                                            @if($p->frecuencia) <span class="italic text-xs"> - {{ $p->frecuencia }}</span> @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <x-slot:actions>
            <x-button label="Imprimir" icon="o-printer" class="btn-primary" onclick="imprimirFichaClinica()" />
            <x-button label="Cerrar" @click="$wire.modalVer = false" class="btn-neutral" />
        </x-slot:actions>
    </x-modal>
</div>
