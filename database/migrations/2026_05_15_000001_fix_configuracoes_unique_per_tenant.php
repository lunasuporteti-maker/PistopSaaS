<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracoes', function (Blueprint $table) {
            // Remove o índice único global em chave (impedia múltiplos tenants)
            $table->dropUnique(['chave']);
            // Garante unicidade por tenant: um tenant não pode ter a mesma chave duas vezes
            $table->unique(['tenant_id', 'chave']);
        });
    }

    public function down(): void
    {
        Schema::table('configuracoes', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'chave']);
            $table->unique(['chave']);
        });
    }
};
