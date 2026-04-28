<div>
    <x-header title="Reportes" subtitle="Análisis de Rendimiento" separator />

    {{-- KPI de Ingresos Mensuales --}}
    <div wire:init="cargarDatos">
        @if($cargando)
            <div class="flex flex-col items-center justify-center h-96 bg-base-100 rounded-2xl border border-base-200 border-dashed mb-8">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <span class="mt-4 text-base-content/50 font-medium tracking-wide">Calculando métricas y analíticas...</span>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-primary/10 p-6 rounded-2xl border border-primary/20 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-primary uppercase tracking-wider mb-1">Ingresos de {{ now()->translatedFormat('F') }}</p>
                            <p class="text-3xl font-black text-primary">S/ {{ number_format($ingresosMes, 2) }}</p>
                        </div>
                        <div class="bg-primary text-white p-3 rounded-xl">
                            <x-icon name="o-banknotes" class="w-6 h-6" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
                
                {{-- Gráfico de Ventas --}}
                <div class="lg:col-span-8">
                    <x-card title="Ingresos (Últimos 7 días)" shadow class="h-96 border border-base-200">
                        <div class="h-full pb-10">
                            <x-chart wire:model="chartVentas" />
                        </div>
                    </x-card>
                </div>

                {{-- Gráfico de Citas --}}
                <div class="lg:col-span-4">
                    <x-card title="Estado Citas (30 días)" shadow class="h-96 border border-base-200">
                        <div class="h-full pb-10">
                            <x-chart wire:model="chartCitas" />
                        </div>
                    </x-card>
                </div>

            </div>

            {{-- Top Productos --}}
            <div class="grid grid-cols-1 gap-8">
                <x-card title="Top 5 Productos/Servicios Más Vendidos" shadow class="border border-base-200">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-base-200/50">
                                <tr>
                                    <th class="w-16 text-center">Pos</th>
                                    <th>Descripción del Producto / Servicio</th>
                                    <th class="text-right">Unidades Vendidas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-200/50">
                                @forelse($topProductos as $index => $producto)
                                    <tr>
                                        <td class="text-center font-bold text-base-content/50">#{{ $index + 1 }}</td>
                                        <td class="font-medium text-base-content">{{ $producto['descripcion'] }}</td>
                                        <td class="text-right">
                                            <div class="badge badge-primary">{{ $producto['total_vendido'] }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-base-content/50 py-8">
                                            <x-icon name="o-archive-box-x-mark" class="w-12 h-12 mx-auto mb-3 opacity-30" />
                                            No hay suficientes datos de ventas para mostrar el ranking.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>
        @endif
    </div>
</div>
