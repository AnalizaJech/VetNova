<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('mascota_id')->constrained('mascotas')->cascadeOnDelete();
            $table->foreignId('veterinario_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Datos de la cita
            $table->dateTime('fecha_hora');
            $table->string('motivo', 150)->comment('Ej. Vacunación, Consulta general, Baño');
            $table->enum('estado', [
                'PENDIENTE',
                'CONFIRMADA', 
                'EN_PROGRESO', 
                'COMPLETADA', 
                'CANCELADA', 
                'NO_ASISTIO'
            ])->default('PENDIENTE');
            
            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices (la búsqueda por fecha será lo más común)
            $table->index(['clinica_id', 'fecha_hora']);
            $table->index('veterinario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
