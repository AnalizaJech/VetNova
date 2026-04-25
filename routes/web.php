<?php

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware('guest')->group(function () {
    Route::get('/', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    // Dashboard: acceso para todos los autenticados
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // ── Clínica ──
    Route::get('/clientes', \App\Livewire\Clientes\Index::class)
        ->name('clientes')->middleware('can:clientes.ver');
    Route::get('/mascotas', \App\Livewire\Mascotas\Index::class)
        ->name('mascotas')->middleware('can:mascotas.ver');
    Route::get('/historias', \App\Livewire\Historias\Index::class)
        ->name('historias')->middleware('can:historias.ver');
    Route::get('/vacunas', \App\Livewire\Vacunas\Index::class)
        ->name('vacunas')->middleware('can:vacunas.ver');

    // ── Agenda ──
    Route::get('/citas', \App\Livewire\Citas\Index::class)
        ->name('citas')->middleware('can:citas.ver');

    // ── Operaciones ──
    Route::get('/inventario', \App\Livewire\Inventario\Index::class)
        ->name('inventario')->middleware('can:inventario.ver');
    Route::get('/caja', \App\Livewire\Caja\Index::class)
        ->name('caja')->middleware('can:caja.ver');
    Route::get('/facturacion', \App\Livewire\Facturacion\Index::class)
        ->name('facturacion')->middleware('can:facturacion.ver');
    Route::get('/hospitalizacion', \App\Livewire\Hospitalizacion\Index::class)
        ->name('hospitalizacion')->middleware('can:hospitalizacion.ver');

    // ── Soporte ──
    Route::get('/recordatorios', \App\Livewire\Recordatorios\Index::class)
        ->name('recordatorios')->middleware('can:recordatorios.ver');
    Route::get('/reportes', \App\Livewire\Reportes\Index::class)
        ->name('reportes')->middleware('can:reportes.ver');

    // ── Administración ──
    Route::get('/usuarios', \App\Livewire\Usuarios\Index::class)
        ->name('usuarios')->middleware('can:usuarios.ver');
    Route::get('/configuracion', \App\Livewire\Configuracion\Index::class)
        ->name('configuracion')->middleware('can:configuracion.ver');

    // ── Ticket de venta (requiere facturacion.ver) ──
    Route::get('/ventas/{venta}/ticket', [\App\Http\Controllers\TicketController::class, 'show'])
        ->name('ventas.ticket')->middleware('can:facturacion.ver');

    // ── Sesión ──
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

