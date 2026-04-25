<div>
    <x-header title="Punto de Venta" subtitle="Caja y Facturación" separator />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {{-- COLUMNA IZQUIERDA: CARRITO --}}
        <div class="lg:col-span-8 flex flex-col gap-4">
            
            {{-- Buscador de Productos (Barcode o Nombre) --}}
            <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-primary/20">
                <x-choices
                    label="Agregar Producto o Servicio"
                    wire:model.live="productoSeleccionado"
                    :options="$productosSearch"
                    search-function="buscarProductos"
                    option-label="nombre"
                    option-value="id"
                    placeholder="Escribe el nombre o escanea el código de barras..."
                    no-result-text="Producto no encontrado o sin stock."
                    searchable
                    single
                    clearable
                    icon="o-magnifying-glass"
                    class="text-lg"
                >
                    {{-- Plantilla personalizada para el dropdown de productos --}}
                    @scope('item', $producto)
                        <x-list-item :item="$producto" value="nombre" sub-value="categoria">
                            <x-slot:actions>
                                <span class="font-bold text-success">S/ {{ $producto->precio_venta }}</span>
                                @if($producto->tipo === 'PRODUCTO')
                                    <x-badge value="Stock: {{ $producto->stock_actual }}" class="badge-neutral badge-sm ml-2" />
                                @endif
                            </x-slot:actions>
                        </x-list-item>
                    @endscope
                </x-choices>
            </div>

            {{-- Lista del Carrito --}}
            <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 overflow-hidden min-h-[400px]">
                <table class="table table-zebra w-full">
                    <thead class="bg-base-200 text-base-content font-bold">
                        <tr>
                            <th>Item</th>
                            <th class="w-32 text-center">Cant.</th>
                            <th class="text-right">Precio Un.</th>
                            <th class="text-right">Total</th>
                            <th class="w-16"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($carrito as $index => $item)
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $item['nombre'] }}</div>
                                    <div class="text-[10px] text-base-content/60 uppercase">{{ $item['tipo'] }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-1">
                                        <button wire:click="actualizarCantidad({{ $index }}, {{ $item['cantidad'] - 1 }})" class="btn btn-xs btn-circle btn-ghost">-</button>
                                        <span class="w-8 text-center font-bold">{{ $item['cantidad'] }}</span>
                                        <button wire:click="actualizarCantidad({{ $index }}, {{ $item['cantidad'] + 1 }})" class="btn btn-xs btn-circle btn-ghost">+</button>
                                    </div>
                                </td>
                                <td class="text-right">S/ {{ number_format($item['precio_unitario'], 2) }}</td>
                                <td class="text-right font-bold text-primary">S/ {{ number_format($item['subtotal'], 2) }}</td>
                                <td class="text-center">
                                    <button wire:click="eliminarDelCarrito({{ $index }})" class="btn btn-xs btn-circle btn-ghost text-error">
                                        <x-icon name="o-trash" class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-base-content/40">
                                    <x-icon name="o-shopping-cart" class="w-12 h-12 mx-auto mb-2 opacity-30" />
                                    Aún no hay productos en la venta actual.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- COLUMNA DERECHA: CONFIGURACIÓN DE PAGO Y TOTALES --}}
        <div class="lg:col-span-4 flex flex-col gap-4">
            
            {{-- Cliente --}}
            <div class="bg-base-100 p-5 rounded-2xl shadow-sm border border-base-200">
                <h3 class="font-bold text-sm mb-3">1. Cliente</h3>
                <x-choices
                    wire:model="cliente_id"
                    :options="$clientesSearch"
                    search-function="buscarClientes"
                    option-label="nombre_completo"
                    option-sub-label="numero_documento"
                    option-value="id"
                    placeholder="Público General"
                    searchable
                    clearable
                    single
                    icon="o-user"
                />
            </div>

            {{-- Facturación --}}
            <div class="bg-base-100 p-5 rounded-2xl shadow-sm border border-base-200">
                <h3 class="font-bold text-sm mb-3">2. Comprobante y Pago</h3>
                <div class="flex flex-col gap-3">
                    <x-select wire:model="tipo_comprobante" :options="$comprobantes" icon="o-document-text" />
                    <x-select wire:model="metodo_pago" :options="$metodos_pago" icon="o-banknotes" />
                </div>
            </div>

            {{-- Totales y Botón de Cobro --}}
            <div class="bg-base-300 p-5 rounded-2xl shadow-md border border-neutral">
                <h3 class="font-bold text-sm mb-4">3. Resumen de Pago</h3>
                
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span>Op. Gravada / Subtotal:</span>
                        <span>S/ {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>IGV (18%):</span>
                        <span>S/ {{ number_format($igv, 2) }}</span>
                    </div>
                    <div class="w-full border-t border-base-content/10 my-2"></div>
                    <div class="flex justify-between text-xl font-black text-primary">
                        <span>TOTAL A PAGAR:</span>
                        <span>S/ {{ number_format($total, 2) }}</span>
                    </div>
                </div>

                @if(empty($carrito))
                    <x-button 
                        label="Agregue productos para cobrar" 
                        icon="o-shopping-cart" 
                        class="btn-primary w-full shadow-lg btn-disabled opacity-50" 
                    />
                @else
                    <x-button 
                        label="Procesar y Cobrar" 
                        icon="o-check-circle" 
                        wire:click="cobrar" 
                        spinner="cobrar" 
                        class="btn-primary w-full shadow-lg" 
                    />
                @endif
            </div>

        </div>
    </div>
</div>
