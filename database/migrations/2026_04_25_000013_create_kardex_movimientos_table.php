<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kardex: historial de movimientos de inventario.
 * Cada entrada/salida de stock queda registrada aquí con trazabilidad completa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kardex_movimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();

            // Tipo de movimiento
            $table->enum('tipo', [
                'ENTRADA_COMPRA',      // Compra a proveedor
                'ENTRADA_AJUSTE',      // Ajuste manual positivo
                'SALIDA_VENTA',        // Venta desde caja
                'SALIDA_DISPENSACION', // Dispensación de prescripción
                'SALIDA_AJUSTE',       // Ajuste manual negativo (merma, vencimiento)
            ]);

            // Cantidades
            $table->integer('cantidad')->comment('Positivo para entradas, negativo para salidas');
            $table->integer('stock_anterior')->comment('Stock antes del movimiento');
            $table->integer('stock_posterior')->comment('Stock después del movimiento');

            // Referencia opcional (ej: venta_id, prescripcion_id)
            $table->string('referencia_tipo', 50)->nullable()->comment('Ej: venta, prescripcion');
            $table->unsignedBigInteger('referencia_id')->nullable();

            $table->text('notas')->nullable();

            $table->timestamps();

            // Índices para consultas rápidas
            $table->index(['clinica_id', 'producto_id']);
            $table->index(['clinica_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kardex_movimientos');
    }
};
