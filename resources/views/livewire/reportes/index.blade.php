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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Métrica 1 --}}
                <div class="bg-primary/5 p-5 rounded-2xl border border-primary/20 shadow-sm">
                    <div class="flex justify-between items-center mb-2">
                        <p class="text-xs font-bold text-primary uppercase tracking-wider">Ingresos {{ now()->translatedFormat('F') }}</p>
                        <x-icon name="o-banknotes" class="w-5 h-5 text-primary" />
                    </div>
                    <p class="text-2xl font-black text-base-content">S/ {{ number_format($ingresosMes, 2) }}</p>
                </div>
                {{-- Métrica 2 --}}
                <div class="bg-info/5 p-5 rounded-2xl border border-info/20 shadow-sm">
                    <div class="flex justify-between items-center mb-2">
                        <p class="text-xs font-bold text-info uppercase tracking-wider">Ticket Promedio</p>
                        <x-icon name="o-receipt-percent" class="w-5 h-5 text-info" />
                    </div>
                    <p class="text-2xl font-black text-base-content">S/ {{ number_format($ticketPromedio, 2) }}</p>
                </div>
                {{-- Métrica 3 --}}
                <div class="bg-success/5 p-5 rounded-2xl border border-success/20 shadow-sm">
                    <div class="flex justify-between items-center mb-2">
                        <p class="text-xs font-bold text-success uppercase tracking-wider">Ventas Realizadas</p>
                        <x-icon name="o-shopping-cart" class="w-5 h-5 text-success" />
                    </div>
                    <p class="text-2xl font-black text-base-content">{{ $totalVentasMes }}</p>
                </div>
                {{-- Métrica 4 --}}
                <div class="bg-warning/5 p-5 rounded-2xl border border-warning/20 shadow-sm">
                    <div class="flex justify-between items-center mb-2">
                        <p class="text-xs font-bold text-warning uppercase tracking-wider">Pacientes Nuevos</p>
                        <x-icon name="o-heart" class="w-5 h-5 text-warning" />
                    </div>
                    <p class="text-2xl font-black text-base-content">{{ $pacientesNuevosMes }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
                {{-- Gráfico de Ventas --}}
                <div class="lg:col-span-8">
                    <x-card title="Ingresos Semanales (S/)" shadow class="h-96 border border-base-200">
                        <div class="h-full pb-10">
                            <x-chart wire:model="chartVentas" />
                        </div>
                    </x-card>
                </div>

                {{-- Gráfico de Citas --}}
                <div class="lg:col-span-4">
                    <x-card title="Estado de Citas (30d)" shadow class="h-96 border border-base-200">
                        <div class="h-full pb-10">
                            <x-chart wire:model="chartCitas" />
                        </div>
                    </x-card>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                {{-- Ventas por Categoría --}}
                <x-card title="Distribución por Categoría" subtitle="Ventas del mes actual" shadow class="h-96 border border-base-200">
                    <div class="h-full pb-10">
                        <x-chart wire:model="chartCategorias" />
                    </div>
                </x-card>

                {{-- Productividad Veterinarios --}}
                <x-card title="Productividad Médica" subtitle="Citas completadas este mes" shadow class="h-96 border border-base-200">
                    <div class="h-full pb-10">
                        <x-chart wire:model="chartProductividad" />
                    </div>
                </x-card>
            </div>

            {{-- Top Productos --}}
            <div class="grid grid-cols-1 gap-8">
                <x-card title="Ranking: Top 5 Productos / Servicios" subtitle="Más vendidos históricamente" shadow class="border border-base-200">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-base-200/50">
                                <tr>
                                    <th class="w-16 text-center">Pos</th>
                                    <th>Descripción</th>
                                    <th class="text-right">Unidades</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-200/50">
                                @forelse($topProductos as $index => $producto)
                                    <tr>
                                        <td class="text-center font-bold text-base-content/40">#{{ $index + 1 }}</td>
                                        <td class="font-semibold text-base-content">{{ $producto['descripcion'] }}</td>
                                        <td class="text-right">
                                            <div class="badge badge-primary font-bold">{{ $producto['total_vendido'] }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-base-content/50 py-10">No hay datos suficientes.</td>
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
