<div>
    {{-- Header de Perfil --}}
    <x-header :title="$mascota->nombre" subtitle="{{ $mascota->especie }} · {{ $mascota->raza ?: 'Sin raza' }} · {{ $mascota->sexo === 'M' ? 'Macho' : 'Hembra' }}" separator>
        <x-slot:actions>
            <x-button icon="o-pencil" class="btn-ghost" link="{{ route('mascotas') }}?id={{ $mascota->id }}&action=edit" label="Editar Perfil" />
            <x-button icon="o-plus" class="btn-primary" link="{{ route('historias', ['search' => $mascota->nombre]) }}" label="Nueva Consulta" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        {{-- Columna Lateral: Info Rápida --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Card de la Mascota --}}
            <x-card class="shadow-sm border-t-4 border-t-primary">
                <div class="flex flex-col items-center py-4">
                    <div class="avatar placeholder mb-4">
                        <div class="bg-primary/10 text-primary rounded-full w-24 flex items-center justify-center border-2 border-primary/20">
                            <x-icon name="o-heart" class="w-12 h-12" />
                        </div>
                    </div>
                    <h2 class="text-xl font-bold">{{ $mascota->nombre }}</h2>
                    <p class="text-sm text-base-content/60">{{ $mascota->edad_readable }}</p>
                    @if($mascota->fallecido)
                        <x-badge value="Fallecido" class="badge-neutral mt-2" />
                    @else
                        <x-badge value="Activo" class="badge-success badge-sm mt-2" />
                    @endif
                </div>

                <x-hr />

                <div class="space-y-4 py-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-base-content/60">Peso Actual:</span>
                        <span class="font-bold">{{ $mascota->peso_actual ?: '0.00' }} kg</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-base-content/60">Color:</span>
                        <span class="font-medium">{{ $mascota->color ?: 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-base-content/60">Esterilizado:</span>
                        <span class="font-medium">{{ $mascota->esterilizado ? 'Sí' : 'No' }}</span>
                    </div>
                </div>
            </x-card>

            {{-- Info Propietario --}}
            <x-card title="Propietario" shadow class="border border-base-200">
                @if($mascota->cliente)
                    <div class="flex flex-col gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-base-content/50 font-bold">Nombre</p>
                            <p class="text-sm font-semibold">{{ $mascota->cliente->nombre_completo }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-base-content/50 font-bold">Teléfono</p>
                            <a href="tel:{{ $mascota->cliente->telefono }}" class="text-sm font-semibold text-primary hover:underline">
                                {{ $mascota->cliente->telefono ?: 'N/A' }}
                            </a>
                        </div>
                        <x-button label="Ver Cliente" link="{{ route('clientes') }}?search={{ $mascota->cliente->numero_documento }}" class="btn-xs btn-outline" />
                    </div>
                @else
                    <p class="text-error text-sm italic">Sin dueño asignado.</p>
                @endif
            </x-card>
        </div>

        {{-- Columna Principal: Historias y Cronología --}}
        <div class="lg:col-span-3">
            <x-tabs selected="historias">
                
                {{-- Pestaña: Historias Clínicas --}}
                <x-tab name="historias" label="Historial Médico" icon="o-clipboard-document-list">
                    <div class="space-y-4 mt-6">
                        @forelse($historias as $hist)
                            <x-card class="border-l-4 border-l-primary hover:shadow-md transition-shadow">
                                <div class="flex flex-col md:flex-row justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <x-badge :value="$hist->fecha->translatedFormat('d M Y')" class="badge-neutral badge-sm font-bold" />
                                            <span class="text-xs text-base-content/40">{{ $hist->veterinario->name ?? 'Veterinario' }}</span>
                                        </div>
                                        <h3 class="font-bold text-lg text-primary">{{ $hist->motivo_consulta }}</h3>
                                        <p class="text-sm text-base-content/70 mt-2 line-clamp-2 italic">"{{ $hist->diagnostico_presuntivo }}"</p>
                                        
                                        @if($hist->prescripciones->isNotEmpty())
                                            <div class="flex flex-wrap gap-2 mt-3">
                                                @foreach($hist->prescripciones as $p)
                                                    <x-badge :value="$p->medicamento" class="badge-outline badge-success badge-xs" />
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-end">
                                        <x-button icon="o-eye" label="Ver completo" wire:click="$dispatch('openModalHistorico', {id: {{ $hist->id }}})" class="btn-sm btn-ghost" />
                                    </div>
                                </div>
                            </x-card>
                        @empty
                            <div class="text-center py-20 bg-base-100 rounded-3xl border border-dashed border-base-300">
                                <x-icon name="o-folder-open" class="w-16 h-16 mx-auto mb-4 opacity-20" />
                                <p class="text-base-content/50">Este paciente aún no registra consultas médicas.</p>
                            </div>
                        @endforelse
                    </div>
                </x-tab>

                {{-- Pestaña: Recordatorios / Vacunas --}}
                <x-tab name="recordatorios" label="Agenda y Vacunas" icon="o-bell">
                    <div class="space-y-4 mt-6">
                        @forelse($recordatorios as $rec)
                            <div class="flex items-center gap-4 bg-base-100 p-4 rounded-xl border border-base-200">
                                <div class="bg-info/10 text-info p-3 rounded-xl">
                                    <x-icon name="o-bell-alert" class="w-6 h-6" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-base-content">{{ $rec->titulo }}</p>
                                    <p class="text-xs text-base-content/60">{{ $rec->descripcion }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-black">{{ $rec->fecha_aviso->format('d/m/Y') }}</p>
                                    <x-badge value="{{ $rec->completado ? 'Enviado' : 'Pendiente' }}" class="{{ $rec->completado ? 'badge-success' : 'badge-warning' }} badge-xs" />
                                </div>
                            </div>
                        @empty
                            <p class="text-center py-10 text-base-content/40 italic">No hay recordatorios pendientes.</p>
                        @endforelse
                    </div>
                </x-tab>

            </x-tabs>
        </div>

    </div>
</div>
