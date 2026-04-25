<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            // Multi-tenant
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            
            // Datos personales
            $table->enum('tipo_documento', ['DNI', 'RUC', 'CE', 'PASAPORTE'])->default('DNI');
            $table->string('numero_documento', 15);
            $table->string('nombres', 100);
            $table->string('apellidos', 100)->nullable(); // Si es RUC (empresa), se usa solo "nombres" como Razón Social
            
            // Contacto
            $table->string('email', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            
            // Ubicación
            $table->string('direccion')->nullable();
            $table->string('codigo_ubigeo', 6)->nullable(); // Código SUNAT directo
            $table->foreignId('distrito_id')->nullable()->constrained('ubigeo_distritos')->nullOnDelete();
            
            // Flags de estado
            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para búsquedas rápidas (por clinica_id siempre primero)
            $table->index(['clinica_id', 'numero_documento']);
            $table->index(['clinica_id', 'nombres', 'apellidos']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
