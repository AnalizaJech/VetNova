<?php

declare(strict_types=1);

namespace App\Livewire\Facturacion;

use App\Models\Venta;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\AlertModal;

#[Layout('components.layouts.app')]
#[Title('Facturación e Historial — VetNova')]
class Index extends Component
{
    use WithPagination;
    use AlertModal;

    public string $search = '';
    public string $filtroFecha = '';

    public function mount()
    {
        $this->filtroFecha = date('Y-m-d'); // Por defecto ver ventas de hoy
    }

    public function render()
    {
        $ventas = Venta::query()
            ->with(['cliente', 'cajero'])
            ->where('clinica_id', auth()->user()->clinica_id)
            ->when($this->filtroFecha, function ($q) {
                $q->whereDate('created_at', $this->filtroFecha);
            })
            ->when($this->search, function ($q) {
                $q->whereHas('cliente', function ($q2) {
                    $q2->where('nombres', 'like', "%{$this->search}%")
                       ->orWhere('numero_documento', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $headers = [
            ['key' => 'id', 'label' => 'Nro Comprobante'],
            ['key' => 'cliente', 'label' => 'Cliente'],
            ['key' => 'tipo_comprobante', 'label' => 'Documento'],
            ['key' => 'total', 'label' => 'Total'],
            ['key' => 'metodo_pago', 'label' => 'Pago'],
            ['key' => 'estado', 'label' => 'Estado']
        ];

        return view('livewire.facturacion.index', [
            'ventas' => $ventas,
            'headers' => $headers
        ]);
    }
}

