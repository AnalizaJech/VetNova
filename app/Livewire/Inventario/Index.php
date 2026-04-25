<?php

declare(strict_types=1);

namespace App\Livewire\Inventario;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\AlertModal;

#[Layout('components.layouts.app')]
#[Title('Catálogo e Inventario — VetNova')]
class Index extends Component
{
    use WithPagination, AlertModal;

    // Filtros
    public string $search = '';
    public string $filtroTipo = '';
    public bool $filtroStockBajo = false;

    // Modal
    public bool $modalModal = false;
    public bool $isEditing = false;

    // Formulario
    public ?int $producto_id = null;
    public string $tipo = 'PRODUCTO';
    public string $categoria = '';
    public string $nombre = '';
    public string $codigo_barras = '';
    public ?float $precio_venta = null;
    public ?float $costo_compra = null;
    public bool $afecto_igv = true;
    public int $stock_actual = 0;
    public int $stock_minimo = 0;
    public bool $activo = true;
    public string $notas = '';

    public array $tipos = [
        ['id' => 'PRODUCTO', 'name' => 'Producto Físico'],
        ['id' => 'SERVICIO', 'name' => 'Servicio Médico / Grooming'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroStockBajo(): void
    {
        $this->resetPage();
    }

    // Limpia los campos de stock si cambia a Servicio
    public function updatedTipo($value): void
    {
        if ($value === 'SERVICIO') {
            $this->stock_actual = 0;
            $this->stock_minimo = 0;
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->modalModal = true;
    }

    public function edit(Producto $producto): void
    {
        $this->resetForm();
        $this->isEditing = true;
        
        $this->producto_id = $producto->id;
        $this->tipo = $producto->tipo;
        $this->categoria = $producto->categoria ?? '';
        $this->nombre = $producto->nombre;
        $this->codigo_barras = $producto->codigo_barras ?? '';
        $this->precio_venta = (float) $producto->precio_venta;
        $this->costo_compra = $producto->costo_compra !== null ? (float) $producto->costo_compra : null;
        $this->afecto_igv = $producto->afecto_igv;
        $this->stock_actual = $producto->stock_actual;
        $this->stock_minimo = $producto->stock_minimo;
        $this->activo = $producto->activo;
        $this->notas = $producto->notas ?? '';

        $this->modalModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'tipo' => 'required|in:PRODUCTO,SERVICIO',
            'nombre' => 'required|string|max:150',
            'categoria' => 'nullable|string|max:100',
            'codigo_barras' => 'nullable|string|max:100',
            'precio_venta' => 'required|numeric|min:0',
            'costo_compra' => 'nullable|numeric|min:0',
            'stock_actual' => 'required_if:tipo,PRODUCTO|integer',
            'stock_minimo' => 'required_if:tipo,PRODUCTO|integer',
        ], [
            'precio_venta.required' => 'El precio de venta es obligatorio.',
        ]);

        $clinica_id = auth()->user()->clinica_id;

        $data = [
            'clinica_id' => $clinica_id,
            'tipo' => $this->tipo,
            'categoria' => $this->categoria,
            'nombre' => $this->nombre,
            'codigo_barras' => $this->codigo_barras,
            'precio_venta' => $this->precio_venta,
            'costo_compra' => $this->costo_compra,
            'afecto_igv' => $this->afecto_igv,
            'stock_actual' => $this->tipo === 'PRODUCTO' ? $this->stock_actual : 0,
            'stock_minimo' => $this->tipo === 'PRODUCTO' ? $this->stock_minimo : 0,
            'activo' => $this->activo,
            'notas' => $this->notas,
        ];

        if ($this->isEditing && $this->producto_id) {
            Producto::where('clinica_id', $clinica_id)->findOrFail($this->producto_id)->update($data);
            $this->success('Item actualizado correctamente.');
        } else {
            Producto::create($data);
            $this->success('Item agregado al catálogo.');
        }

        $this->modalModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $producto = Producto::where('clinica_id', auth()->user()->clinica_id)->findOrFail($id);
        $producto->delete();
        $this->warning('Eliminado del catálogo.');
    }

    private function resetForm(): void
    {
        $this->reset([
            'producto_id', 'categoria', 'nombre', 'codigo_barras', 
            'precio_venta', 'costo_compra', 'notas'
        ]);
        $this->tipo = 'PRODUCTO';
        $this->stock_actual = 0;
        $this->stock_minimo = 0;
        $this->afecto_igv = true;
        $this->activo = true;
    }

    // Configuración de tabla Mary UI
    public function headers(): array
    {
        return [
            ['key' => 'item', 'label' => 'Item / Servicio'],
            ['key' => 'precio', 'label' => 'Precio'],
            ['key' => 'stock', 'label' => 'Stock', 'class' => 'w-24 text-center'],
            ['key' => 'estado', 'label' => 'Estado', 'class' => 'w-24'],
        ];
    }

    public function getProductosProperty(): LengthAwarePaginator
    {
        return Producto::query()
            ->where('clinica_id', auth()->user()->clinica_id)
            ->when($this->filtroTipo, function (Builder $query) {
                $query->where('tipo', $this->filtroTipo);
            })
            ->when($this->filtroStockBajo, function (Builder $query) {
                $query->where('tipo', 'PRODUCTO')->whereRaw('stock_actual <= stock_minimo');
            })
            ->when($this->search, function (Builder $query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                      ->orWhere('codigo_barras', 'like', "%{$this->search}%")
                      ->orWhere('categoria', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('nombre', 'asc')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.inventario.index', [
            'productos' => $this->productos,
            'headers' => $this->headers(),
        ]);
    }
}


