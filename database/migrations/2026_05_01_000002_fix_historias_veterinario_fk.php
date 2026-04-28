<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            // Solo intentar eliminar si existe (Laravel 11 way)
            $fks = collect(Schema::getForeignKeys('historias_clinicas'));
            if ($fks->contains('name', 'historias_clinicas_veterinario_id_foreign')) {
                $table->dropForeign(['veterinario_id']);
            }
            
            // Hacer la columna nullable y cambiar la FK a nullOnDelete
            $table->unsignedBigInteger('veterinario_id')->nullable()->change();
            
            $table->foreign('veterinario_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->dropForeign(['veterinario_id']);
            $table->foreign('veterinario_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
