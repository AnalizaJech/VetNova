<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            
            // Datos básicos
            $table->string('nombre', 100);
            $table->string('especie', 50); // Ej: Perro, Gato, Ave, Roedor, Exótico
            $table->string('raza', 100)->nullable();
            $table->enum('sexo', ['M', 'H'])->comment('M = Macho, H = Hembra');
            $table->string('color', 50)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->decimal('peso_actual', 8, 2)->nullable()->comment('Peso en kg');
            $table->string('foto')->nullable();
            
            // Estado médico rápido
            $table->boolean('esterilizado')->default(false);
            $table->boolean('fallecido')->default(false);
            $table->text('notas_medicas')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para búsquedas rápidas
            $table->index(['clinica_id', 'nombre']);
            $table->index('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};
