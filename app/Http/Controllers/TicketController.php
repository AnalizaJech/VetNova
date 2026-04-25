<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Muestra el ticket de venta formateado para tiquetera térmica de 80mm.
     */
    public function show(Venta $venta)
    {
        // Asegurarse de que la venta pertenece a la clínica actual
        if ($venta->clinica_id !== auth()->user()->clinica_id) {
            abort(403, 'No tienes permiso para ver este ticket.');
        }

        $venta->load(['cliente', 'cajero', 'detalles']);

        return view('tickets.venta', compact('venta'));
    }
}
