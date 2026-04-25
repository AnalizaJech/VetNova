<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade campos de clínica y sucursal a la tabla users.
 * Vincula cada usuario a una clínica (tenant) y opcionalmente a una sucursal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('clinica_id')->nullable()->after('id')->constrained('clinicas')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->after('clinica_id')->constrained('sucursales')->nullOnDelete();
            $table->string('telefono', 20)->nullable()->after('email');
            $table->string('dni', 8)->nullable()->after('telefono');
            $table->string('avatar')->nullable()->after('dni');
            $table->boolean('activo')->default(true)->after('avatar');
            $table->softDeletes();

            $table->index('clinica_id');
            $table->index('sucursal_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinica_id']);
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn(['clinica_id', 'sucursal_id', 'telefono', 'dni', 'avatar', 'activo', 'deleted_at']);
        });
    }
};
