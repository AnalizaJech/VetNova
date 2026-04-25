<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tablas de ubigeo peruano (departamento → provincia → distrito).
 * Se pueblan una sola vez con el UbigeoSeeder desde eApi Perú.
 * En runtime, los dropdowns consultan estas tablas locales.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Departamentos del Perú (25)
        Schema::create('ubigeo_departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo', 2)->unique();
        });

        // Provincias (196 aprox)
        Schema::create('ubigeo_provincias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('ubigeo_departamentos')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->string('codigo', 4)->unique();

            $table->index('departamento_id');
        });

        // Distritos (1874 aprox)
        Schema::create('ubigeo_distritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provincia_id')->constrained('ubigeo_provincias')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->string('codigo', 6);
            $table->string('codigo_ubigeo', 6)->unique();

            $table->index('provincia_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubigeo_distritos');
        Schema::dropIfExists('ubigeo_provincias');
        Schema::dropIfExists('ubigeo_departamentos');
    }
};
