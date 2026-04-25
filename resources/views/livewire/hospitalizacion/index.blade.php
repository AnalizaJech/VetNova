<div>
    <x-header title="Hospitalización" subtitle="Control de pacientes internados en clínica" separator>
        <x-slot:actions>
            <x-button icon="o-plus" class="btn-primary" wire:click="abrirIngreso" label="Nuevo Ingreso" />
        </x-slot:actions>
    </x-header>

    {{-- Grid de Pacientes Internados --}}
    @if($internados->isEmpty())
        <div class="text-center py-16 bg-base-100 rounded-2xl border border-base-200 border-dashed">
            <x-icon name="o-heart" class="w-16 h-16 mx-auto mb-4 opacity-30" />
            <h3 class="text-lg font-bold text-base-content/70">Sin pacientes internados</h3>
            <p class="text-base-content/50">No hay ninguna mascota ocupando jaulas en este momento.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($internados as $hosp)
                <x-card class="shadow-sm border-l-4 border-l-warning">
                    
                    {{-- Cabecera Card --}}
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="bg-warning/20 text-warning rounded-full w-12 flex items-center justify-center">
                                    <x-icon name="o-star" class="w-6 h-6" />
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg leading-none">{{ $hosp->mascota->nombre }}</h3>
                                <p class="text-xs text-base-content/60 mt-1 truncate max-w-[150px]">
                                    Dueño: {{ $hosp->mascota->cliente->nombres ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <x-badge value="{{ $hosp->jaula ? 'Cama: ' . $hosp->jaula : 'Sin cama' }}" class="badge-neutral shadow-sm font-bold" />
                        </div>
                    </div>

                    {{-- Motivo --}}
                    <div class="text-sm bg-base-200/50 p-3 rounded-lg mb-4">
                        <p class="font-semibold text-xs text-base-content/60 uppercase mb-1">Motivo de Ingreso</p>
                        <p class="text-base-content">{{ $hosp->motivo_ingreso }}</p>
                    </div>

                    {{-- Fecha y Hora --}}
                    <div class="text-xs text-base-content/50 mb-4 flex items-center gap-2">
                        <x-icon name="o-clock" class="w-4 h-4" />
                        Ingresó: {{ $hosp->fecha_ingreso->diffForHumans() }} 
                        <span class="opacity-50">({{ $hosp->fecha_ingreso->format('d/m h:i A') }})</span>
                    </div>

                    {{-- Botones de Acción --}}
                    <x-slot:actions>
                        <div class="flex w-full gap-2">
                            <x-button label="Notas Clínicas" icon="o-document-text" wire:click="verNotas({{ $hosp->id }})" class="btn-sm btn-ghost text-info flex-1" />
                            
                            <x-button label="Dar de Alta" icon="o-check-circle" 
                                wire:confirm="¿Estás seguro que deseas dar de alta al paciente {{ $hosp->mascota->nombre }}? Esto liberará su cama."
                                wire:click="darDeAlta({{ $hosp->id }})" 
                                class="btn-sm btn-success text-white" />
                        </div>
                    </x-slot:actions>
                </x-card>
            @endforeach
        </div>
    @endif

    {{-- MODAL: Nuevo Ingreso --}}
    <x-modal wire:model="modalIngreso" title="Ingresar Paciente a Hospitalización" separator>
        <x-form wire:submit="guardarIngreso">
            
            <x-choices
                label="Seleccionar Mascota"
                wire:model="mascota_id"
                :options="$mascotasSearch"
                search-function="buscarMascotas"
                option-label="nombre"
                option-sub-label="cliente.nombre_completo"
                option-value="id"
                placeholder="Busca el nombre del paciente..."
                no-result-text="No se encontraron mascotas"
                searchable
                single
                icon="o-magnifying-glass"
            />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <x-input label="Motivo Clínico de Ingreso" wire:model="motivo_ingreso" placeholder="Ej. Observación post-quirúrgica, parvovirosis..." />
                </div>
                <div>
                    <x-input label="Jaula / Cama (Opcional)" wire:model="jaula" placeholder="Ej. A1, UCI-3" icon="o-hashtag" />
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.modalIngreso = false" class="btn-ghost" />
                <x-button label="Ingresar Paciente" type="submit" class="btn-primary" icon="o-arrow-right-on-rectangle" spinner="guardarIngreso" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- MODAL: Notas de Evolución --}}
    <x-modal wire:model="modalNotas" title="Evolución Clínica - {{ $hospActual?->mascota->nombre }}" separator class="backdrop-blur-sm">
        
        <div class="flex flex-col gap-4 max-h-96 overflow-y-auto pr-2 mb-4">
            @if($hospActual && $hospActual->notas->isEmpty())
                <p class="text-center text-sm text-base-content/50 italic py-4">No hay notas de evolución aún.</p>
            @elseif($hospActual)
                @foreach($hospActual->notas as $nota)
                    <div class="bg-base-200 p-3 rounded-lg relative">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-bold text-sm text-primary">{{ $nota->veterinario->name }}</span>
                            <span class="text-[10px] text-base-content/50">{{ $nota->created_at->format('d/m/Y h:i A') }}</span>
                        </div>
                        <p class="text-sm text-base-content/80 whitespace-pre-wrap">{{ $nota->nota }}</p>
                    </div>
                @endforeach
            @endif
        </div>

        <x-hr />

        <x-form wire:submit="guardarNota">
            <x-textarea label="Nueva Nota de Evolución" wire:model="nuevaNota" placeholder="Temperatura, FC, FR, estado de ánimo, medicamentos administrados..." rows="3" />
            
            <x-slot:actions>
                <x-button label="Cerrar" @click="$wire.modalNotas = false" class="btn-ghost" />
                <x-button label="Guardar Nota" type="submit" class="btn-info text-white" icon="o-pencil" spinner="guardarNota" />
            </x-slot:actions>
        </x-form>
    </x-modal>

</div>
