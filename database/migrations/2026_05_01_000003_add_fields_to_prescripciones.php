<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescripciones', function (Blueprint $table) {
            if (!Schema::hasColumn('prescripciones', 'frecuencia')) {
                $table->string('frecuencia')->nullable()->after('dosis');
            }
            if (!Schema::hasColumn('prescripciones', 'duracion')) {
                $table->string('duracion')->nullable()->after('frecuencia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescripciones', function (Blueprint $table) {
            $table->dropColumn(['frecuencia', 'duracion']);
        });
    }
};
