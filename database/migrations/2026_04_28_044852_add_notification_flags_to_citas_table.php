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
        Schema::table('citas', function (Blueprint $table) {
            $table->boolean('notificado_sms')->default(false)->after('notas');
            $table->boolean('notificado_whatsapp')->default(false)->after('notificado_sms');
            $table->boolean('notificado_email')->default(false)->after('notificado_whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn(['notificado_sms', 'notificado_whatsapp', 'notificado_email']);
        });
    }
};
