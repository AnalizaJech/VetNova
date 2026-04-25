<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cabecera de la Venta
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            
            // Cliente puede ser nulo para ventas rápidas por "Ticket"
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('cajero_id')->constrained('users')->cascadeOnDelete();
            
            // Datos del Comprobante
            $table->enum('tipo_comprobante', ['TICKET', 'BOLETA', 'FACTURA'])->default('TICKET');
            $table->string('serie_correlativo', 50)->nullable()->comment('Ej: B001-000123');
            
            // Totales
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('igv', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            // Pago
            $table->enum('metodo_pago', ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'YAPE_PLIN'])->default('EFECTIVO');
            $table->enum('estado', ['PAGADO', 'ANULADO'])->default('PAGADO');
            
            // Integración Nubefact
            $table->string('nubefact_enlace_pdf')->nullable();
            $table->string('nubefact_external_id')->nullable();
            
            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['clinica_id', 'created_at']);
        });

        // 2. Detalles de la Venta (El "Carrito")
        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            
            // Snapshot del momento (por si luego cambian el nombre o precio del producto original)
            $table->string('descripcion', 150);
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->boolean('afecto_igv')->default(true);
            $table->decimal('subtotal', 10, 2);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_detalles');
        Schema::dropIfExists('ventas');
    }
};
