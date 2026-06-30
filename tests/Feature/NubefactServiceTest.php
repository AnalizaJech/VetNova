<?php

declare(strict_types=1);

use App\Models\Clinica;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\NubefactService;
use Illuminate\Support\Facades\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('valida que una boleta se formatea sin errores si no hay cliente (null-safe)', function () {
    // 1. Preparar datos
    $clinica = Clinica::create([
        'nombre' => 'VetNeoLink Test',
        'ruc' => '20123456789',
        'razon_social' => 'VetNeoLink Test SAC',
    ]);

    $cajero = User::create([
        'clinica_id' => $clinica->id,
        'name' => 'Cajero Test',
        'email' => 'cajero@test.com',
        'password' => bcrypt('password')
    ]);
    
    $venta = Venta::create([
        'clinica_id' => $clinica->id,
        'sucursal_id' => 1,
        'cajero_id' => $cajero->id,
        'cliente_id' => null, 
        'tipo_comprobante' => 'BOLETA',
        'serie' => 'B001',
        'correlativo' => '1',
        'total' => 100.00,
        'igv' => 15.25,
        'subtotal' => 84.75,
        'estado' => 'PAGADO',
        'metodo_pago' => 'EFECTIVO'
    ]);

    $producto = Producto::create([
        'clinica_id' => $clinica->id,
        'nombre' => 'Producto Prueba',
        'tipo' => 'PRODUCTO',
        'precio_venta' => 100.00,
        'stock_actual' => 10,
        'stock_minimo' => 5
    ]);

    VentaDetalle::create([
        'venta_id' => $venta->id,
        'producto_id' => $producto->id,
        'descripcion' => 'Producto Prueba',
        'cantidad' => 1,
        'precio_unitario' => 100.00,
        'subtotal' => 100.00,
        'afecto_igv' => true
    ]);

    // 2. Mock de la API de Nubefact para no hacer llamadas reales
    Http::fake([
        'api.nubefact.com/*' => Http::response([
            'tipo_de_comprobante' => 2,
            'serie' => 'B001',
            'numero' => 1,
            'enlace_del_pdf' => 'https://test.pdf'
        ], 200),
    ]);

    // 3. Ejecutar
    config(['services.nubefact.url' => 'https://api.nubefact.com/api/v1/invoice']);
    config(['services.nubefact.token' => 'fake_token']);
    $service = new NubefactService();
    
    $resultado = $service->emitir($venta);

    // 4. Afirmar
    expect($resultado)->toBeArray()
        ->and($resultado['exito'])->toBeTrue()
        ->and($resultado['enlace_pdf'])->toBe('https://test.pdf');
});

it('valida que una factura exige un cliente con RUC', function () {
    $clinica = Clinica::create([
        'nombre' => 'VetNeoLink Test',
        'ruc' => '20123456789',
        'razon_social' => 'VetNeoLink Test SAC',
    ]);

    $cajero = User::create([
        'clinica_id' => $clinica->id,
        'name' => 'Cajero Test 2',
        'email' => 'cajero2@test.com',
        'password' => bcrypt('password')
    ]);
    
    $venta = Venta::create([
        'clinica_id' => $clinica->id,
        'sucursal_id' => 1,
        'cajero_id' => $cajero->id,
        'cliente_id' => null, 
        'tipo_comprobante' => 'FACTURA',
        'serie' => 'F001',
        'correlativo' => '1',
        'total' => 100.00,
        'igv' => 15.25,
        'subtotal' => 84.75,
        'estado' => 'PAGADO',
        'metodo_pago' => 'EFECTIVO'
    ]);

    config(['services.nubefact.url' => 'https://api.nubefact.com/api/v1/invoice']);
    config(['services.nubefact.token' => 'fake_token']);
    $service = new NubefactService();
    $resultado = $service->emitir($venta);

    expect($resultado['exito'])->toBeFalse()
        ->and($resultado['error'])->toBe('Una FACTURA exige tener un cliente con RUC.');
});
