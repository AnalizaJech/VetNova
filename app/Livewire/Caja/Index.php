<?php

declare(strict_types=1);

namespace App\Livewire\Caja;

use App\Models\Cliente;
use App\Models\KardexMovimiento;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Traits\AlertModal;

#[Layout('components.layouts.app')]
#[Title('Caja y Facturación — VetNeoLink')]
class Index extends Component
{
    use AlertModal;

    // Carrito de compras
    public array $carrito = [];
    public float $subtotal = 0;
    public float $igv = 0;
    public float $total = 0;

    // Buscadores
    public ?int $cliente_id = null;
    public ?int $productoSeleccionado = null;
    public Collection|array $clientesSearch = [];
    public Collection|array $productosSearch = [];

    // Opciones de Venta
    public string $tipo_comprobante = 'TICKET';
    public string $metodo_pago = 'EFECTIVO';
    public string $notas = '';

    public array $metodos_pago = [
        ['id' => 'EFECTIVO', 'name' => 'Efectivo'],
        ['id' => 'TARJETA', 'name' => 'Tarjeta (POS)'],
        ['id' => 'TRANSFERENCIA', 'name' => 'Transferencia Bancaria'],
        ['id' => 'YAPE_PLIN', 'name' => 'Yape / Plin'],
    ];

    public function mount(): void
    {
        $this->buscarClientes('');
        $this->buscarProductos('');
    }

    public function buscarClientes(string $value = ''): void
    {
        $this->clientesSearch = Cliente::query()
            ->where('clinica_id', Auth::user()->clinica_id)
            ->where('activo', true)
            ->when($value, function (Builder $query) use ($value) {
                $query->where(function ($q) use ($value) {
                    $q->where('nombres', 'like', "%{$value}%")
                      ->orWhere('apellidos', 'like', "%{$value}%")
                      ->orWhere('numero_documento', 'like', "%{$value}%");
                });
            })
            ->take(10)
            ->get();
    }

    public function buscarProductos(string $value = ''): void
    {
        $this->productosSearch = Producto::query()
            ->where('clinica_id', Auth::user()->clinica_id)
            ->where('activo', true)
            ->when($value, function (Builder $query) use ($value) {
                $query->where(function ($q) use ($value) {
                    $q->where('nombre', 'like', "%{$value}%")
                      ->orWhere('codigo_barras', 'like', "%{$value}%");
                });
            })
            ->take(15)
            ->get();
    }

    /**
     * Cuando seleccionamos un producto en el buscador, lo añadimos al carrito.
     */
    public function updatedProductoSeleccionado($id): void
    {
        if (!$id) return;

        $producto = Producto::find($id);
        
        if ($producto) {
            // Verificar stock mínimo vital para productos físicos
            if ($producto->tipo === 'PRODUCTO' && $producto->stock_actual <= 0) {
                $this->error('Este producto no tiene stock disponible.');
                $this->productoSeleccionado = null;
                return;
            }

            // Buscar si ya está en el carrito para sumar la cantidad
            $existenteIndex = collect($this->carrito)->search(fn($item) => $item['producto_id'] === $producto->id);
            
            if ($existenteIndex !== false) {
                // Verificar que la nueva cantidad no supere el stock si es producto físico
                if ($producto->tipo === 'PRODUCTO' && ($this->carrito[$existenteIndex]['cantidad'] + 1) > $producto->stock_actual) {
                    $this->error("Stock insuficiente. Solo quedan {$producto->stock_actual} uds.");
                } else {
                    $this->carrito[$existenteIndex]['cantidad']++;
                    $this->carrito[$existenteIndex]['subtotal'] = $this->carrito[$existenteIndex]['cantidad'] * $this->carrito[$existenteIndex]['precio_unitario'];
                }
            } else {
                $this->carrito[] = [
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'tipo' => $producto->tipo,
                    'cantidad' => 1,
                    'precio_unitario' => (float) $producto->precio_venta,
                    'subtotal' => (float) $producto->precio_venta,
                    'afecto_igv' => $producto->afecto_igv,
                    'stock_actual' => $producto->stock_actual,
                ];
            }
            $this->calcularTotales();
        }
        
        // Limpiar el selector para permitir buscar de nuevo
        $this->productoSeleccionado = null;
    }

    public function eliminarDelCarrito(int $index): void
    {
        unset($this->carrito[$index]);
        $this->carrito = array_values($this->carrito); // Reindexar el array
        $this->calcularTotales();
    }

    public function actualizarCantidad(int $index, int $nuevaCantidad): void
    {
        if ($nuevaCantidad < 1) return;
        
        $item = $this->carrito[$index];
        
        if ($item['tipo'] === 'PRODUCTO' && $nuevaCantidad > $item['stock_actual']) {
            $this->error("No hay stock suficiente. Solo hay {$item['stock_actual']}.");
            return;
        }

        $this->carrito[$index]['cantidad'] = $nuevaCantidad;
        $this->carrito[$index]['subtotal'] = $nuevaCantidad * $item['precio_unitario'];
        $this->calcularTotales();
    }

    private function calcularTotales(): void
    {
        $subtotal = 0;
        $igv = 0;
        $total = 0;

        foreach ($this->carrito as $item) {
            $itemTotal = $item['subtotal'];
            $total += $itemTotal;
            
            if ($item['afecto_igv']) {
                // En Perú, los precios suelen incluir el IGV de cara al consumidor.
                // Subtotal base = Total / 1.18
                $base = $itemTotal / 1.18;
                $itemIgv = $itemTotal - $base;
                $subtotal += $base;
                $igv += $itemIgv;
            } else {
                $subtotal += $itemTotal;
            }
        }

        $this->subtotal = round($subtotal, 2);
        $this->igv = round($igv, 2);
        $this->total = round($total, 2);
    }

    public function cobrar()
    {
        if (!$this->cliente_id) {
            $this->error('Debe seleccionar un cliente para procesar la venta.');
            return;
        }

        if (empty($this->carrito)) {
            $this->warning('El carrito está vacío.');
            return;
        }

        $clinica_id = Auth::user()->clinica_id;

        $venta = DB::transaction(function () use ($clinica_id) {
            // 1. Crear cabecera de la venta
            $nuevaVenta = Venta::create([
                'clinica_id' => $clinica_id,
                'cliente_id' => $this->cliente_id,
                'cajero_id' => Auth::id(),
                'subtotal' => $this->subtotal,
                'igv' => $this->igv,
                'total' => $this->total,
                'metodo_pago' => $this->metodo_pago,
                'estado' => 'PAGADO',
                'notas' => $this->notas,
            ]);

            // 2. Crear detalles y descontar stock
            foreach ($this->carrito as $item) {
                VentaDetalle::create([
                    'venta_id' => $nuevaVenta->id,
                    'producto_id' => $item['producto_id'],
                    'descripcion' => $item['nombre'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'afecto_igv' => $item['afecto_igv'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Descontar stock con lock para evitar race conditions
                if ($item['tipo'] === 'PRODUCTO') {
                    $producto = Producto::where('id', $item['producto_id'])->lockForUpdate()->first();
                    
                    if ($producto) {
                        $stockAnterior = $producto->stock_actual;
                        $producto->decrement('stock_actual', $item['cantidad']);
                        
                        // Registrar en Kardex
                        KardexMovimiento::create([
                            'clinica_id' => $nuevaVenta->clinica_id,
                            'producto_id' => $producto->id,
                            'usuario_id' => Auth::id(),
                            'tipo' => 'SALIDA_VENTA',
                            'cantidad' => -$item['cantidad'],
                            'stock_anterior' => $stockAnterior,
                            'stock_posterior' => $stockAnterior - $item['cantidad'],
                            'referencia_tipo' => 'venta',
                            'referencia_id' => $nuevaVenta->id,
                            'notas' => 'Venta en caja'
                        ]);
                    }
                }
            }
            
            return $nuevaVenta;
        });

        $this->success('¡Venta registrada con éxito!');
        
        // Redirigir a la vista del ticket
        return redirect()->route('ventas.ticket', $venta->id);
    }

    private function limpiarCaja(): void
    {
        $this->carrito = [];
        $this->cliente_id = null;
        $this->subtotal = 0;
        $this->igv = 0;
        $this->total = 0;
        $this->notas = '';
        $this->tipo_comprobante = 'TICKET';
        $this->metodo_pago = 'EFECTIVO';
        
        // Refrescar buscadores
        $this->buscarClientes('');
        $this->buscarProductos('');
    }

    public function render()
    {
        return view('livewire.caja.index');
    }
}


