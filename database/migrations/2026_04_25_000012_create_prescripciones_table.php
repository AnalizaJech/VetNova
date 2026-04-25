<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prescripciones médicas vinculadas a historias clínicas.
 * Permiten registrar medicamentos recetados y opcionalmente
 * descontar stock del inventario cuando se dispensan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescripciones', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('historia_clinica_id')->constrained('historias_clinicas')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();

            // Datos de la prescripción
            $table->string('medicamento', 150)->comment('Nombre del medicamento o producto');
            $table->string('dosis', 100)->comment('Ej: 1 tableta cada 8 horas');
            $table->string('via_administracion', 50)->default('ORAL')->comment('ORAL, TOPICA, INYECTABLE, etc.');
            $table->integer('duracion_dias')->nullable()->comment('Duración del tratamiento en días');
            $table->text('indicaciones')->nullable()->comment('Instrucciones especiales');

            // Control de dispensación
            $table->integer('cantidad_dispensada')->default(0)->comment('Unidades entregadas del inventario');
            $table->boolean('dispensado')->default(false)->comment('Si ya se entregó el medicamento');

            $table->timestamps();

            // Índice para consultar prescripciones por historia
            $table->index(['clinica_id', 'historia_clinica_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescripciones');
    }
};
