<?php

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware('guest')->group(function () {
    Route::get('/', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/citas', \App\Livewire\Citas\Index::class)->name('citas');
    Route::get('/clientes', \App\Livewire\Clientes\Index::class)->name('clientes');
    Route::get('/mascotas', \App\Livewire\Mascotas\Index::class)->name('mascotas');
    Route::get('/historias', \App\Livewire\Historias\Index::class)->name('historias');
    Route::get('/vacunas', \App\Livewire\Vacunas\Index::class)->name('vacunas');
    Route::get('/inventario', \App\Livewire\Inventario\Index::class)->name('inventario');
    Route::get('/caja', \App\Livewire\Caja\Index::class)->name('caja');
    Route::get('/ventas/{venta}/ticket', [\App\Http\Controllers\TicketController::class, 'show'])->name('ventas.ticket');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
