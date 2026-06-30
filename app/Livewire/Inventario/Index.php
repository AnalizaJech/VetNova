<?php

declare(strict_types=1);

namespace App\Livewire\Inventario;

use App\Models\KardexMovimiento;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\AlertModal;

#[Layout('components.layouts.app')]
#[Title('Catálogo e Inventario — VetNeoLink')]
class Index extends Component
{
    use WithPagination, AlertModal;

    // Filtros
    public string $search = '';
    public string $filtroTipo = '';
    public bool $filtroStockBajo = false;

    // Modales
    public bool $modalModal = false;
    public bool $isEditing = false;
    public bool $modalKardex = false;
    public ?Producto $kardexProducto = null;

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

    // Trazabilidad inicial/ajuste
    public string $lote = '';
    public ?string $fecha_vencimiento = null;
    public string $documento_referencia = '';

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

        /** @var User $authUser */
        $authUser = Auth::user();
        $clinica_id = $authUser->clinica_id;

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

        // Lógica para Kardex
        $diferencia_stock = 0;
        $stock_anterior = 0;
        $tipo_movimiento = null;

        if ($this->isEditing && $this->producto_id) {
            $producto = Producto::where('clinica_id', $clinica_id)->findOrFail($this->producto_id);
            
            if ($this->tipo === 'PRODUCTO') {
                $stock_anterior = $producto->stock_actual;
                $diferencia_stock = $this->stock_actual - $stock_anterior;
                
                if ($diferencia_stock > 0) {
                    $tipo_movimiento = 'ENTRADA_AJUSTE';
                } elseif ($diferencia_stock < 0) {
                    $tipo_movimiento = 'SALIDA_AJUSTE';
                }
            }

            $producto->update($data);
            $producto_id_final = $producto->id;
            
            $this->success('Item actualizado correctamente.');
        } else {
            $producto = Producto::create($data);
            $producto_id_final = $producto->id;
            
            if ($this->tipo === 'PRODUCTO' && $this->stock_actual > 0) {
                $diferencia_stock = $this->stock_actual;
                $stock_anterior = 0;
                $tipo_movimiento = 'ENTRADA_COMPRA'; // Inventario inicial
            }
            
            $this->success('Item agregado al catálogo.');
        }

        // Registrar en el Kardex si hubo un cambio de stock
        if ($diferencia_stock !== 0 && $tipo_movimiento) {
            KardexMovimiento::create([
                'clinica_id' => $clinica_id,
                'producto_id' => $producto_id_final,
                'usuario_id' => Auth::id(),
                'tipo' => $tipo_movimiento,
                'cantidad' => $diferencia_stock,
                'costo_unitario' => $this->costo_compra,
                'lote' => $this->lote ?: null,
                'fecha_vencimiento' => $this->fecha_vencimiento ?: null,
                'documento_referencia' => $this->documento_referencia ?: null,
                'stock_anterior' => $stock_anterior,
                'stock_posterior' => $stock_anterior + $diferencia_stock,
                'notas' => $this->isEditing ? ($this->notas ?: 'Ajuste manual de stock') : 'Inventario inicial',
            ]);
        }

        $this->modalModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        $producto = Producto::where('clinica_id', $authUser->clinica_id)->findOrFail($id);
        $producto->delete();
        $this->warning('Eliminado del catálogo.');
    }

    public function verKardex(int $id): void
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        $this->kardexProducto = Producto::where('clinica_id', $authUser->clinica_id)
            ->where('tipo', 'PRODUCTO')
            ->findOrFail($id);
            
        $this->modalKardex = true;
    }

    private function resetForm(): void
    {
        $this->reset([
            'producto_id', 'categoria', 'nombre', 'codigo_barras', 
            'precio_venta', 'costo_compra', 'notas',
            'lote', 'fecha_vencimiento', 'documento_referencia'
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
        /** @var User $authUser */
        $authUser = Auth::user();

        return Producto::query()
            ->where('clinica_id', $authUser->clinica_id)
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

    public function getKardexProperty()
    {
        if (!$this->kardexProducto) return collect();

        return KardexMovimiento::with('usuario')
            ->where('producto_id', $this->kardexProducto->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.inventario.index', [
            'productos' => $this->productos,
            'headers' => $this->headers(),
        ]);
    }
}


