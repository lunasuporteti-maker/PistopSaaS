<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pecas', function (Blueprint $table) {
            // Especificacoes / aplicacoes da peca (ex.: veiculos compativeis)
            $table->text('especificacoes')->nullable()->after('nome');
        });
    }

    public function down(): void
    {
        Schema::table('pecas', function (Blueprint $table) {
            $table->dropColumn('especificacoes');
        });
    }
};
