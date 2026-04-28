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
        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'nubefact_enlace_pdf')) {
                $table->dropColumn('nubefact_enlace_pdf');
            }
            if (Schema::hasColumn('ventas', 'nubefact_external_id')) {
                $table->dropColumn('nubefact_external_id');
            }
            if (Schema::hasColumn('ventas', 'serie_correlativo')) {
                $table->dropColumn('serie_correlativo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('nubefact_enlace_pdf')->nullable();
            $table->string('nubefact_external_id')->nullable();
            $table->string('serie_correlativo')->nullable();
        });
    }
};
