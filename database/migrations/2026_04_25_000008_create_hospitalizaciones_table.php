<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitalizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('mascota_id')->constrained('mascotas')->cascadeOnDelete();
            
            $table->string('jaula', 50)->nullable()->comment('Identificador de la jaula o cama');
            $table->string('motivo_ingreso', 255);
            
            $table->dateTime('fecha_ingreso');
            $table->dateTime('fecha_alta')->nullable();
            
            $table->enum('estado', ['INTERNADO', 'DE_ALTA', 'FALLECIDO'])->default('INTERNADO');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['clinica_id', 'estado']);
        });

        Schema::create('hospitalizacion_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospitalizacion_id')->constrained('hospitalizaciones')->cascadeOnDelete();
            $table->foreignId('veterinario_id')->constrained('users')->cascadeOnDelete();
            
            $table->text('nota');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitalizacion_notas');
        Schema::dropIfExists('hospitalizaciones');
    }
};
