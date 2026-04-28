<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kardex_movimientos', function (Blueprint $table) {
            $table->decimal('costo_unitario', 10, 2)->nullable()->after('cantidad')->comment('Costo al momento del movimiento');
            $table->string('lote', 50)->nullable()->after('costo_unitario')->comment('Lote del producto (trazabilidad)');
            $table->date('fecha_vencimiento')->nullable()->after('lote')->comment('Fecha de caducidad');
            $table->string('documento_referencia', 50)->nullable()->after('fecha_vencimiento')->comment('Factura o Guía de Remisión');
        });
    }

    public function down(): void
    {
        Schema::table('kardex_movimientos', function (Blueprint $table) {
            $table->dropColumn(['costo_unitario', 'lote', 'fecha_vencimiento', 'documento_referencia']);
        });
    }
};
