<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Producto;
use App\Models\Venta;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard — VetNova')]
class Dashboard extends Component
{
    public function render()
    {
        $clinica_id = auth()->user()->clinica_id;
        $hoy = Carbon::today();

        // 1. Estadísticas Generales
        $ventasHoy = Venta::where('clinica_id', $clinica_id)
            ->whereDate('created_at', $hoy)
            ->where('estado', 'PAGADO')
            ->sum('total');

        $citasHoy = Cita::where('clinica_id', $clinica_id)
            ->whereDate('fecha', $hoy)
            ->count();

        $nuevosPacientes = Mascota::where('clinica_id', $clinica_id)
            ->whereMonth('created_at', $hoy->month)
            ->whereYear('created_at', $hoy->year)
            ->count();

        $alertasStock = Producto::where('clinica_id', $clinica_id)
            ->where('tipo', 'PRODUCTO')
            ->where('activo', true)
            ->whereRaw('stock_actual <= stock_minimo')
            ->count();

        // 2. Tablas Rápidas
        $proximasCitas = Cita::with(['mascota.cliente'])
            ->where('clinica_id', $clinica_id)
            ->whereDate('fecha', $hoy)
            ->whereIn('estado', ['PENDIENTE', 'EN_PROGRESO'])
            ->orderBy('hora_inicio', 'asc')
            ->take(5)
            ->get();

        $ultimasVentas = Venta::with(['cliente'])
            ->where('clinica_id', $clinica_id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('livewire.dashboard', [
            'ventasHoy' => (float) $ventasHoy,
            'citasHoy' => $citasHoy,
            'nuevosPacientes' => $nuevosPacientes,
            'alertasStock' => $alertasStock,
            'proximasCitas' => $proximasCitas,
            'ultimasVentas' => $ultimasVentas,
        ]);
    }
}
