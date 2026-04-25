<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historias_clinicas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('mascota_id')->constrained('mascotas')->cascadeOnDelete();
            $table->foreignId('veterinario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            
            // Datos generales de la consulta
            $table->dateTime('fecha');
            $table->string('motivo_consulta');
            
            // Constantes fisiológicas (Triage)
            $table->decimal('peso', 8, 2)->nullable()->comment('En kg');
            $table->decimal('temperatura', 4, 1)->nullable()->comment('En °C');
            $table->integer('frecuencia_cardiaca')->nullable()->comment('Latidos por minuto');
            $table->integer('frecuencia_respiratoria')->nullable()->comment('Respiraciones por minuto');
            
            // Evaluación médica
            $table->text('anamnesis')->nullable()->comment('Síntomas, historia previa');
            $table->text('diagnostico_presuntivo')->nullable();
            $table->text('tratamiento_indicaciones')->nullable()->comment('Receta médica o procedimiento a seguir');
            
            // Control
            $table->date('proxima_cita_recomendada')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices (Búsqueda rápida por paciente)
            $table->index(['clinica_id', 'mascota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historias_clinicas');
    }
};
