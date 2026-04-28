<div>
    {{-- Header --}}
    <x-header title="Medicina Preventiva" subtitle="Control de vacunas y desparasitaciones" separator>
        <x-slot:actions>
            <x-button icon="o-plus" class="btn-primary" wire:click="create" label="Nuevo Registro" responsive />
        </x-slot:actions>
    </x-header>

    <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-200 mb-6 flex flex-wrap items-center gap-4">
        <div class="hidden md:flex items-center gap-2">
            <x-icon name="o-funnel" class="w-5 h-5 text-primary/70" />
            <span class="font-bold text-sm">Filtros:</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1">
            <x-select wire:model.live="filtroTipo" :options="$tipos" placeholder="Todos los tipos" icon="o-tag" class="select-sm" />
            <x-input icon="o-magnifying-glass" placeholder="Buscar mascota o producto..." wire:model.live.debounce.500ms="search" clearable class="input-sm md:col-span-2" />
        </div>
    </div>

    {{-- Tabla --}}
    <x-card class="shadow-sm">
        <x-table :headers="$headers" :rows="$registros" with-pagination>
            
            {{-- Columna Fecha --}}
            @scope('cell_fecha', $registro)
                <div class="font-bold text-base-content">{{ $registro->fecha_aplicacion->translatedFormat('d M Y') }}</div>
            @endscope

            {{-- Columna Paciente --}}
            @scope('cell_paciente', $registro)
                <div class="font-semibold text-primary flex items-center gap-2">
                    {{ $registro->mascota->nombre ?? 'N/A' }}
                </div>
                <div class="text-xs text-base-content/60 mt-1">
                    {{ $registro->mascota->especie ?? '' }} &bull; Dueño: {{ $registro->mascota->cliente->nombres ?? '' }}
                </div>
            @endscope

            {{-- Columna Detalle --}}
            @scope('cell_detalle', $registro)
                <div class="flex items-center gap-2 mb-1">
                    @php $badge = $registro->tipo_badge; @endphp
                    <x-badge :value="$badge['label']" class="{{ $badge['class'] }} badge-sm" />
                </div>
                <div class="font-medium text-sm">{{ $registro->producto_o_enfermedad }}</div>
                @if($registro->lote_marca)
                    <div class="text-xs text-base-content/60 mt-1">Lote/Marca: {{ $registro->lote_marca }}</div>
                @endif
            @endscope

            {{-- Columna Próxima Dosis --}}
            @scope('cell_proxima', $registro)
                @if($registro->fecha_proxima)
                    @php
                        $isOverdue = $registro->fecha_proxima->isPast();
                        $isSoon = $registro->fecha_proxima->isBetween(now(), now()->addDays(7));
                    @endphp
                    <div class="flex items-center gap-2">
                        <x-icon name="o-calendar" class="w-4 h-4 {{ $isOverdue ? 'text-error' : ($isSoon ? 'text-warning' : 'text-base-content/50') }}" />
                        <span class="font-medium {{ $isOverdue ? 'text-error' : ($isSoon ? 'text-warning' : '') }}">
                            {{ $registro->fecha_proxima->translatedFormat('d M Y') }}
                        </span>
                    </div>
                    @if($isOverdue)
                        <div class="text-[10px] text-error font-bold mt-1 uppercase">Vencida</div>
                    @endif
                @else
                    <span class="text-xs text-base-content/40 italic">No programada</span>
                @endif
            @endscope

            {{-- Acciones --}}
            @scope('actions', $registro)
                <div class="flex items-center gap-1">
                    <x-button icon="o-pencil" wire:click="edit({{ $registro->id }})" class="btn-ghost btn-sm text-info" tooltip="Editar" />
                    <x-button icon="o-trash" class="btn-ghost btn-sm text-error" tooltip="Eliminar"
                        wire:confirm="¿Seguro que deseas eliminar este registro médico?"
                        wire:click="delete({{ $registro->id }})" />
                </div>
            @endscope
        </x-table>
        
        @if($registros->isEmpty())
            <div class="text-center py-10 text-base-content/50">
                <x-icon name="o-shield-check" class="w-12 h-12 mx-auto mb-3 opacity-20" />
                <p>No hay registros preventivos aún.</p>
            </div>
        @endif
    </x-card>

    {{-- Modal de Crear / Editar --}}
    <x-modal wire:model="modalModal" :title="$isEditing ? 'Editar Registro' : 'Nuevo Registro Preventivo'" separator class="backdrop-blur-sm">
        
        <x-form wire:submit="save">
            
            {{-- Paciente --}}
            <div class="bg-base-200/50 p-4 rounded-xl border border-base-200 mb-4">
                <x-choices
                    label="Paciente"
                    wire:model="mascota_id"
                    :options="$mascotasSearch"
                    search-function="buscarMascotas"
                    option-label="nombre"
                    option-sub-label="descripcion_selector"
                    option-value="id"
                    placeholder="Busca por mascota o dueño..."
                    searchable
                    single
                    icon="o-heart"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-select label="Tipo de Procedimiento" wire:model="tipo" :options="$tipos" required />
                <x-input label="Producto o Vacuna" wire:model="producto_o_enfermedad" required placeholder="Ej. Triple Felina, Bravecto..." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="Lote o Marca comercial" wire:model="lote_marca" placeholder="Opcional" />
                <x-input label="Peso actual (kg)" wire:model="peso_al_momento" type="number" step="0.01" icon="o-scale" hint="Para dosificación y control" />
            </div>

            <x-hr />

            {{-- Fechas --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="Fecha de Aplicación" wire:model.live="fecha_aplicacion" type="date" required icon="o-calendar" />
                
                <div class="flex flex-col">
                    <x-input label="Próxima Dosis (Recordatorio)" wire:model="fecha_proxima" type="date" icon="o-bell" />
                    {{-- Botones rápidos de cálculo de fecha --}}
                    <div class="flex gap-2 mt-2">
                        <button type="button" wire:click="setProximaFecha(1)" class="btn btn-xs btn-outline btn-neutral">En 1 mes</button>
                        <button type="button" wire:click="setProximaFecha(3)" class="btn btn-xs btn-outline btn-neutral">En 3 meses</button>
                        <button type="button" wire:click="setProximaFecha(12)" class="btn btn-xs btn-outline btn-neutral">En 1 año</button>
                    </div>
                </div>
            </div>

            <x-textarea label="Notas u Observaciones" wire:model="notas" rows="2" placeholder="Reacciones adversas, estado general..." />

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.modalModal = false" class="btn-ghost" />
                <x-button label="Guardar" type="submit" class="btn-primary" icon="o-check" spinner="save" />
            </x-slot:actions>
        </x-form>

    </x-modal>
</div>
