<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            
            // Tenant
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            
            // Clasificación
            $table->enum('tipo', ['PRODUCTO', 'SERVICIO'])->default('PRODUCTO');
            $table->string('categoria', 100)->nullable()->comment('Ej. Medicamentos, Consultas, Alimentos, Accesorios');
            
            // Identificación
            $table->string('nombre', 150);
            $table->string('codigo_barras', 100)->nullable();
            
            // Precios y Costos
            $table->decimal('precio_venta', 10, 2);
            $table->decimal('costo_compra', 10, 2)->nullable();
            $table->boolean('afecto_igv')->default(true)->comment('Importante para SUNAT (Nubefact)');
            
            // Stock (Solo aplica si tipo == PRODUCTO)
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0)->comment('Para generar alertas de reposición');
            
            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para búsquedas rápidas al momento de facturar
            $table->index(['clinica_id', 'nombre']);
            $table->index(['clinica_id', 'codigo_barras']);
            $table->index(['clinica_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
