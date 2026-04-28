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
    public array $topProductos = [];
    public float $ingresosMes = 0;
    public bool $cargando = true;
    public string $errorMensaje = '';

    public function mount(): void {}

    public function cargarDatos(): void
    {
        $clinica_id = auth()->user()->clinica_id;
        
        // 1. Ingresos últimos 7 días (Optimizado: 1 sola query)
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
                'datasets' => [
                    [
                        'label' => 'Ingresos (S/)',
                        'data' => $totales,
                        'backgroundColor' => '#10b981', // emerald-500
                        'borderRadius' => 6,
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['display' => false]
                ]
            ]
        ];

        // 2. Citas por estado (últimos 30 días)
        $estadosCitas = Cita::select('estado', DB::raw('count(*) as count'))
            ->where('clinica_id', $clinica_id)
            ->where('fecha_hora', '>=', Carbon::today()->subDays(30))
            ->groupBy('estado')
            ->get();

        $citasLabels = $estadosCitas->pluck('estado')->toArray();
        $citasData = $estadosCitas->pluck('count')->toArray();
        
        // Colores semánticos para el Donut
        $colores = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6b7280', '#8b5cf6'];

        $this->chartCitas = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $citasLabels,
                'datasets' => [
                    [
                        'data' => $citasData,
                        'backgroundColor' => array_slice($colores, 0, count($citasData)),
                        'borderWidth' => 0,
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'cutout' => '70%',
            ]
        ];

        // 3. Top 5 Productos más vendidos
        $this->topProductos = VentaDetalle::select('producto_id', 'descripcion', DB::raw('SUM(cantidad) as total_vendido'))
            ->whereHas('venta', function ($q) use ($clinica_id) {
                $q->where('clinica_id', $clinica_id)->where('estado', 'PAGADO');
            })
            ->groupBy('producto_id', 'descripcion')
            ->orderByDesc('total_vendido')
            ->take(5)
            ->get()
            ->toArray();

        // 4. Ingresos del mes actual
        $this->ingresosMes = (float) Venta::where('clinica_id', $clinica_id)
            ->where('estado', 'PAGADO')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        $this->cargando = false;
    }

    public function render()
    {
        return view('livewire.reportes.index');
    }
}

