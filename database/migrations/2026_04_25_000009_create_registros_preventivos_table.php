<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_preventivos', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('mascota_id')->constrained('mascotas')->cascadeOnDelete();
            $table->foreignId('veterinario_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Tipo de prevención
            $table->enum('tipo', ['VACUNA', 'DESPARASITACION_INT', 'DESPARASITACION_EXT']);
            
            // Detalles médicos
            $table->string('producto_o_enfermedad', 150)->comment('Ej. Quíntuple, Bravecto, Nexgard');
            $table->string('lote_marca', 100)->nullable();
            $table->decimal('peso_al_momento', 8, 2)->nullable();
            
            // Fechas clave
            $table->date('fecha_aplicacion');
            $table->date('fecha_proxima')->nullable()->comment('Fecha para el siguiente recordatorio');
            
            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para reportes y recordatorios rápidos
            $table->index(['clinica_id', 'mascota_id']);
            $table->index(['clinica_id', 'fecha_proxima']); // Súper útil para el cronjob de recordatorios
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_preventivos');
    }
};
