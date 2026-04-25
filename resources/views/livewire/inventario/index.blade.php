<div>
    {{-- Header --}}
    <x-header title="Catálogo e Inventario" subtitle="Gestión de productos, medicinas y servicios" separator>
        <x-slot:middle class="!justify-end gap-2">
            <x-toggle label="Stock Bajo" wire:model.live="filtroStockBajo" class="toggle-warning toggle-sm" />
            <x-select wire:model.live="filtroTipo" :options="$tipos" placeholder="Todos" class="w-32 md:w-48" icon="o-funnel" />
            <x-input icon="o-magnifying-glass" placeholder="Buscar producto o código..." wire:model.live.debounce.500ms="search" clearable class="w-full md:w-64" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-plus" class="btn-primary" wire:click="create" label="Nuevo Item" responsive />
        </x-slot:actions>
    </x-header>

    {{-- Tabla principal --}}
    <x-card class="shadow-sm">
        <x-table :headers="$headers" :rows="$productos" with-pagination>
            
            {{-- Columna Item --}}
            @scope('cell_item', $item)
                <div class="flex items-center gap-3">
                    <div class="avatar placeholder hidden sm:flex">
                        <div class="bg-base-200 text-base-content rounded-xl w-10">
                            @if($item->tipo === 'SERVICIO')
                                <x-icon name="o-sparkles" class="w-5 h-5 text-info" />
                            @else
                                <x-icon name="o-cube" class="w-5 h-5 text-neutral" />
                            @endif
                        </div>
                    </div>
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
                        <span class="font-bold text-lg {{ $item->stock_bajo ? 'text-error' : 'text-base-content' }}">
                            {{ $item->stock_actual }}
                        </span>
                        @if($item->stock_bajo)
                            <span class="text-[10px] text-error uppercase font-semibold">Bajo</span>
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
                    <x-button icon="o-trash" class="btn-ghost btn-sm text-error" tooltip="Eliminar"
                        wire:confirm="¿Seguro que deseas eliminar {{ $item->nombre }}?"
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

            <x-input label="Nombre / Descripción" wire:model="nombre" required placeholder="Ej. Vacuna Quíntuple Zoetis" autocomplete="off" class="mb-4" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <x-input label="Precio de Venta (S/)" wire:model="precio_venta" type="number" step="0.01" required icon="o-currency-dollar" class="font-bold text-success" />
                <x-input label="Costo de Compra (S/)" wire:model="costo_compra" type="number" step="0.01" icon="o-banknotes" hint="Opcional, para calcular ganancias" />
            </div>

            <x-hr />

            @if($tipo === 'PRODUCTO')
                <h3 class="text-sm font-semibold text-base-content/70 mb-3">Control de Inventario</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <x-input label="Código de Barras / SKU" wire:model="codigo_barras" placeholder="Ej. 775123..." />
                    <x-input label="Stock Actual" wire:model="stock_actual" type="number" required />
                    <x-input label="Stock Mínimo (Alerta)" wire:model="stock_minimo" type="number" required />
                </div>
            @endif

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
                        <th>Usuario</th>
                        <th>Cant.</th>
                        <th>Stock Resultante</th>
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
                            <td>{{ $mov->usuario->name ?? 'Sistema' }}</td>
                            <td class="font-bold {{ $mov->cantidad > 0 ? 'text-success' : 'text-error' }}">
                                {{ $mov->cantidad > 0 ? '+' : '' }}{{ $mov->cantidad }}
                            </td>
                            <td class="font-mono">{{ $mov->stock_posterior }}</td>
                            <td class="text-xs text-base-content/60 max-w-[150px] truncate" title="{{ $mov->notas }}">
                                {{ $mov->notas ?? '-' }}
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
