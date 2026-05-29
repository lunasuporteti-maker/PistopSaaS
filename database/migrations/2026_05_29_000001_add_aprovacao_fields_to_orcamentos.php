<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            // Canal de aprovação: portal | interno | whatsapp (string para portabilidade SQLite/PG)
            $table->string('aprovado_por_canal', 20)->nullable()->after('aprovado_em');
            // IPv6 cabe em 45 chars
            $table->string('aprovado_ip', 45)->nullable()->after('aprovado_por_canal');
            $table->string('aprovado_user_agent', 500)->nullable()->after('aprovado_ip');
        });
    }

    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->dropColumn(['aprovado_por_canal', 'aprovado_ip', 'aprovado_user_agent']);
        });
    }
};
