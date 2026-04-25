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

    public function mount()
    {
        $clinica_id = auth()->user()->clinica_id;
        
        // 1. Ingresos últimos 7 días
        $fechas = [];
        $totales = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::today()->subDays($i);
            $fechas[] = $fecha->format('d/m');
            $total = Venta::where('clinica_id', $clinica_id)
                ->whereDate('created_at', $fecha)
                ->where('estado', 'PAGADO')
                ->sum('total');
            $totales[] = (float) $total;
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
    }

    public function render()
    {
        return view('livewire.reportes.index');
    }
}

