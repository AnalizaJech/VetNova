<?php

declare(strict_types=1);

namespace App\Livewire\Reportes;

use App\Models\Cita;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Reportes y Analíticas — VetNova')]
class Index extends Component
{
    public array $chartVentas = [];
    public array $chartCitas = [];
    public array $chartCategorias = [];
    public array $chartProductividad = [];
    public array $topProductos = [];
    public float $ingresosMes = 0;
    public float $ticketPromedio = 0;
    public int $totalVentasMes = 0;
    public int $pacientesNuevosMes = 0;
    public bool $cargando = true;
    public string $errorMensaje = '';

    public function mount(): void {}

    public function cargarDatos(): void
    {
        $clinica_id = auth()->user()->clinica_id;
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();
        
        // 1. Ingresos últimos 7 días
        $ventasPorDia = Venta::select(
                DB::raw('DATE(created_at) as dia'),
                DB::raw('SUM(total) as total')
            )
            ->where('clinica_id', $clinica_id)
            ->where('estado', 'PAGADO')
            ->whereBetween('created_at', [
                Carbon::today()->subDays(6)->startOfDay(),
                Carbon::today()->endOfDay()
            ])
            ->groupBy('dia')
            ->orderBy('dia', 'asc')
            ->get()
            ->keyBy('dia');

        $fechas = [];
        $totales = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::today()->subDays($i);
            $key = $fecha->format('Y-m-d');
            $fechas[] = $fecha->format('d/m');
            $totales[] = (float) ($ventasPorDia[$key]->total ?? 0);
        }

        $this->chartVentas = [
            'type' => 'bar',
            'data' => [
                'labels' => $fechas,
                'datasets' => [[
                    'label' => 'Ingresos (S/)',
                    'data' => $totales,
                    'backgroundColor' => '#10b981',
                    'borderRadius' => 6,
                ]]
            ],
            'options' => ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['display' => false]]]
        ];

        // 2. Citas por estado (últimos 30 días)
        $estadosCitas = Cita::select('estado', DB::raw('count(*) as count'))
            ->where('clinica_id', $clinica_id)
            ->where('fecha_hora', '>=', Carbon::today()->subDays(30))
            ->groupBy('estado')
            ->get();

        $this->chartCitas = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $estadosCitas->pluck('estado')->toArray(),
                'datasets' => [[
                    'data' => $estadosCitas->pluck('count')->toArray(),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6b7280'],
                    'borderWidth' => 0,
                ]]
            ],
            'options' => ['responsive' => true, 'maintainAspectRatio' => false, 'cutout' => '75%']
        ];

        // 3. Ventas por Categoría (Mes Actual)
        $ventasCat = VentaDetalle::join('productos', 'venta_detalles.producto_id', '=', 'productos.id')
            ->select('productos.categoria', DB::raw('SUM(venta_detalles.subtotal) as total'))
            ->whereHas('venta', fn($q) => $q->where('clinica_id', $clinica_id)->where('estado', 'PAGADO')->whereBetween('created_at', [$inicioMes, $finMes]))
            ->groupBy('productos.categoria')
            ->get();

        $this->chartCategorias = [
            'type' => 'pie',
            'data' => [
                'labels' => $ventasCat->pluck('categoria')->toArray(),
                'datasets' => [[
                    'data' => $ventasCat->pluck('total')->toArray(),
                    'backgroundColor' => ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#f97316'],
                ]]
            ],
            'options' => ['responsive' => true, 'maintainAspectRatio' => false]
        ];

        // 4. Productividad: Citas Atendidas por Veterinario (Mes Actual)
        $prodVets = Cita::join('users', 'citas.veterinario_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->where('citas.clinica_id', $clinica_id)
            ->where('citas.estado', 'COMPLETADA')
            ->whereBetween('citas.fecha_hora', [$inicioMes, $finMes])
            ->groupBy('users.name')
            ->get();

        $this->chartProductividad = [
            'type' => 'bar',
            'data' => [
                'labels' => $prodVets->pluck('name')->toArray(),
                'datasets' => [[
                    'label' => 'Citas Completadas',
                    'data' => $prodVets->pluck('total')->toArray(),
                    'backgroundColor' => '#6366f1',
                    'borderRadius' => 4,
                ]]
            ],
            'options' => ['responsive' => true, 'maintainAspectRatio' => false, 'indexAxis' => 'y']
        ];

        // 5. Métricas de Resumen
        $ventasMesQuery = Venta::where('clinica_id', $clinica_id)
            ->where('estado', 'PAGADO')
            ->whereBetween('created_at', [$inicioMes, $finMes]);

        $this->ingresosMes = (float) $ventasMesQuery->sum('total');
        $this->totalVentasMes = $ventasMesQuery->count();
        $this->ticketPromedio = $this->totalVentasMes > 0 ? $this->ingresosMes / $this->totalVentasMes : 0;
        
        $this->pacientesNuevosMes = \App\Models\Mascota::where('clinica_id', $clinica_id)
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->count();

        // 6. Top Productos
        $this->topProductos = VentaDetalle::select('producto_id', 'descripcion', DB::raw('SUM(cantidad) as total_vendido'))
            ->whereHas('venta', fn($q) => $q->where('clinica_id', $clinica_id)->where('estado', 'PAGADO'))
            ->groupBy('producto_id', 'descripcion')
            ->orderByDesc('total_vendido')
            ->take(5)->get()->toArray();

        $this->cargando = false;
    }

    public function render()
    {
        return view('livewire.reportes.index');
    }
}

