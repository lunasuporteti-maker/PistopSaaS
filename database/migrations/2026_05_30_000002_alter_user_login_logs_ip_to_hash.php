<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_login_logs', function (Blueprint $table) {
            // Aumenta para varchar(64) para comportar hash sha256 (64 hex chars)
            // Dados anteriores (IPs em texto) são descartados — não há registros em produção ainda
            $table->string('ip_address', 64)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_login_logs', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->change();
        });
    }
};
