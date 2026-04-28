<div>
    {{-- Header --}}
    <x-header title="Catálogo e Inventario" subtitle="Gestión de productos y servicios" separator>
        <x-slot:actions>
            <x-button icon="o-plus" class="btn-primary" wire:click="create" label="Nuevo Item" responsive />
        </x-slot:actions>
    </x-header>

    <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-200 mb-6 flex flex-wrap items-center gap-4">
        <div class="hidden md:flex items-center gap-2">
            <x-icon name="o-funnel" class="w-5 h-5 text-primary/70" />
            <span class="font-bold text-sm">Filtros:</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1">
            <x-select wire:model.live="filtroTipo" :options="$tipos" placeholder="Todos los tipos" icon="o-tag" class="select-sm" />
            <x-toggle label="Sólo Stock Bajo" wire:model.live="filtroStockBajo" class="toggle-warning toggle-sm" />
            <x-input icon="o-magnifying-glass" placeholder="Buscar por nombre o código..." wire:model.live.debounce.500ms="search" clearable class="input-sm" />
        </div>
    </div>

    {{-- Tabla principal --}}
    <x-card class="shadow-sm">
        <x-table :headers="$headers" :rows="$productos" with-pagination>
            
            {{-- Columna Item --}}
            @scope('cell_item', $item)
                <div class="flex items-center gap-3">
                    <div>
                        <div class="font-bold text-base-content">{{ $item->nombre }}</div>
                        <div class="text-xs text-base-content/60 flex gap-2 mt-1">
                            @if($item->codigo_barras)
                                <span class="font-mono">#{{ $item->codigo_barras }}</span>
                            @endif
                            @if($item->categoria)
                                <span>&bull; {{ $item->categoria }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endscope

            {{-- Columna Precio --}}
            @scope('cell_precio', $item)
                <div class="font-semibold text-success">S/ {{ number_format($item->precio_venta, 2) }}</div>
                @if(!$item->afecto_igv)
                    <div class="text-[10px] text-base-content/50 uppercase">Exonerado IGV</div>
                @endif
            @endscope

            {{-- Columna Stock --}}
            @scope('cell_stock', $item)
                @if($item->tipo === 'SERVICIO')
                    <span class="text-base-content/30 italic text-xs">N/A</span>
                @else
                    <div class="flex flex-col items-center">
                        <x-badge value="{{ $item->stock_actual }}" class="{{ $item->stock_actual <= $item->stock_minimo ? 'badge-error' : 'badge-neutral' }} font-bold text-lg p-3" />
                        @if($item->stock_actual <= $item->stock_minimo)
                            <span class="text-[10px] text-error uppercase font-black mt-1 animate-pulse">Bajo stock</span>
                        @endif
                    </div>
                @endif
            @endscope

            {{-- Columna Estado --}}
            @scope('cell_estado', $item)
                @if($item->activo)
                    <x-badge value="Activo" class="badge-success badge-sm" />
                @else
                    <x-badge value="Inactivo" class="badge-neutral badge-sm" />
                @endif
            @endscope

            {{-- Acciones --}}
            @scope('actions', $item)
                <div class="flex items-center gap-1">
                    @if($item->tipo === 'PRODUCTO')
                        <x-button icon="o-clipboard-document-list" wire:click="verKardex({{ $item->id }})" class="btn-ghost btn-sm text-primary" tooltip="Ver Kardex" spinner />
                    @endif
                    <x-button icon="o-pencil" wire:click="edit({{ $item->id }})" class="btn-ghost btn-sm text-info" tooltip="Editar" spinner />
                    <x-button icon="o-archive-box-x-mark" class="btn-ghost btn-sm text-error" tooltip="Archivar / Inactivar"
                        wire:confirm="¿Seguro que deseas inactivar {{ $item->nombre }}?"
                        wire:click="delete({{ $item->id }})" spinner />
                </div>
            @endscope
        </x-table>
        
        @if($productos->isEmpty())
            <div class="text-center py-10 text-base-content/50">
                <x-icon name="o-archive-box" class="w-12 h-12 mx-auto mb-3 opacity-20" />
                <p>No se encontraron productos o servicios.</p>
            </div>
        @endif
    </x-card>

    {{-- Modal de Crear / Editar --}}
    <x-modal wire:model="modalModal" :title="$isEditing ? 'Editar Item' : 'Nuevo Producto / Servicio'" separator class="backdrop-blur-sm">
        
        <x-form wire:submit="save">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <x-select label="Tipo de Item" wire:model.live="tipo" :options="$tipos" required />
                
                @php
                $categorias_fijas = [
                    ['id' => 'Medicamento', 'name' => 'Medicamento'],
                    ['id' => 'Accesorio', 'name' => 'Accesorio'],
                    ['id' => 'Alimento', 'name' => 'Alimento'],
                    ['id' => 'Consulta', 'name' => 'Consulta'],
                    ['id' => 'Grooming', 'name' => 'Grooming'],
                    ['id' => 'Laboratorio', 'name' => 'Laboratorio'],
                    ['id' => 'Otro', 'name' => 'Otro'],
                ];
                @endphp
                <x-select label="Categoría" wire:model="categoria" :options="$categorias_fijas" required />
            </div>

            <div class="mb-4">
                <x-input 
                    label="Nombre / Descripción" 
                    wire:model="nombre" 
                    required 
                    placeholder="Ej. Vacuna Quíntuple Zoetis" 
                    autocomplete="off" 
                    icon="o-pencil-square"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <x-input label="Precio de Venta (S/)" wire:model="precio_venta" type="number" step="0.01" required icon="o-currency-dollar" class="font-bold text-success" />
                <x-input label="Costo de Compra (S/)" wire:model="costo_compra" type="number" step="0.01" icon="o-banknotes" hint="Opcional, para calcular ganancias" />
            </div>

            <x-hr />

            @if($tipo === 'PRODUCTO')
                <h3 class="text-sm font-semibold text-base-content/70 mb-3">Control de Inventario</h3>
                <div class="mb-4">
                    <x-input label="Código de Barras / SKU" wire:model="codigo_barras" placeholder="Ej. 775123..." />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Stock Actual" wire:model="stock_actual" type="number" icon="o-cube" />
                    <x-input label="Stock Mínimo (Alerta)" wire:model="stock_minimo" type="number" icon="o-bell-alert" />
                </div>

                {{-- ── SECCIÓN DE TRAZABILIDAD (NUEVO) ── --}}
                <div class="bg-base-200/50 p-4 rounded-xl space-y-4 border border-base-300 mt-4">
                    <div class="flex items-center gap-2 mb-2 text-sm font-bold text-primary">
                        <x-icon name="o-finger-print" class="w-4 h-4" />
                        Trazabilidad y Referencia (Opcional)
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-input label="Lote" wire:model="lote" placeholder="EJ: LOT-2024" icon="o-hashtag" />
                        <x-datepicker label="Vencimiento" wire:model="fecha_vencimiento" icon="o-calendar" :config="['altFormat' => 'd/m/Y']" />
                        <x-input label="Ref. Documento" wire:model="documento_referencia" placeholder="Factura/Guía" icon="o-document-text" />
                    </div>
                    <p class="text-[10px] text-base-content/50 italic">
                        * Estos datos se registrarán en el movimiento de Kardex generado por este cambio de stock.
                    </p>
                </div>
            @endif

            <div class="mt-4">
                <x-textarea label="Notas / Descripción" wire:model="notas" placeholder="Información adicional del producto o servicio..." rows="3" />
            </div>

            <x-hr />

            <div class="flex flex-col gap-3">
                <x-toggle label="Afecto a IGV (18%)" wire:model="afecto_igv" class="toggle-info" hint="Déjalo activo para comprobantes SUNAT estándar." />
                <x-toggle label="Item Activo" wire:model="activo" class="toggle-success" />
            </div>

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.modalModal = false" class="btn-ghost" />
                <x-button label="Guardar" type="submit" class="btn-primary" icon="o-check" spinner="save" />
            </x-slot:actions>
        </x-form>

    </x-modal>

    {{-- ═══════════ MODAL KARDEX ═══════════ --}}
    <x-modal wire:model="modalKardex" title="Kardex de Movimientos" subtitle="{{ $kardexProducto?->nombre }}" separator box-class="max-w-4xl" class="backdrop-blur-sm">
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <x-stat title="Stock Actual" value="{{ $kardexProducto?->stock_actual }}" icon="o-cube" />
            <x-stat title="Costo" value="S/ {{ number_format((float) $kardexProducto?->costo_compra, 2) }}" icon="o-banknotes" />
            <x-stat title="Precio Venta" value="S/ {{ number_format((float) $kardexProducto?->precio_venta, 2) }}" icon="o-currency-dollar" class="text-success" />
        </div>

        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Lote/Venc.</th>
                        <th>Cant.</th>
                        <th>Costo Unit.</th>
                        <th>Stock Res.</th>
                        <th>Ref.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->kardex as $mov)
                        <tr>
                            <td class="whitespace-nowrap">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <x-badge value="{{ $mov->tipo_badge['label'] }}" class="{{ $mov->tipo_badge['class'] }} badge-sm" />
                            </td>
                            <td class="text-xs">
                                @if($mov->lote)
                                    <div class="font-bold">Lote: {{ $mov->lote }}</div>
                                @endif
                                @if($mov->fecha_vencimiento)
                                    <div class="{{ $mov->fecha_vencimiento->isPast() ? 'text-error' : 'text-base-content/70' }}">
                                        Vence: {{ $mov->fecha_vencimiento->format('d/m/Y') }}
                                    </div>
                                @endif
                                @if(!$mov->lote && !$mov->fecha_vencimiento)
                                    -
                                @endif
                            </td>
                            <td class="font-bold {{ $mov->cantidad > 0 ? 'text-success' : 'text-error' }}">
                                {{ $mov->cantidad > 0 ? '+' : '' }}{{ $mov->cantidad }}
                            </td>
                            <td class="font-mono">
                                {{ $mov->costo_unitario ? 'S/ '.number_format((float)$mov->costo_unitario, 2) : '-' }}
                            </td>
                            <td class="font-mono">{{ $mov->stock_posterior }}</td>
                            <td class="text-xs text-base-content/60">
                                <div class="font-bold">{{ $mov->documento_referencia }}</div>
                                <div class="truncate max-w-[100px]" title="{{ $mov->notas }}">{{ $mov->notas }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-base-content/50">
                                No hay movimientos registrados para este producto.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-slot:actions>
            <x-button label="Cerrar" @click="$wire.modalKardex = false" class="btn-ghost" />
        </x-slot:actions>
    </x-modal>
</div>
