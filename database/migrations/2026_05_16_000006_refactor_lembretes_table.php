<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembretes', function (Blueprint $table) {
            // Torna cliente_id opcional (lembrete pode ser geral)
            $table->dropForeign(['cliente_id']);
            $table->unsignedBigInteger('cliente_id')->nullable()->change();
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();

            // data_servico não é mais obrigatório
            $table->dateTime('data_servico')->nullable()->change();

            // Adiciona título para lembretes gerais e status concluído
            $table->string('titulo', 200)->nullable()->after('os_id');
            $table->text('observacao')->nullable()->after('data_lembrete');
        });
    }

    public function down(): void
    {
        Schema::table('lembretes', function (Blueprint $table) {
            $table->dropColumn(['titulo', 'observacao']);
            $table->dateTime('data_servico')->nullable(false)->change();
            $table->dropForeign(['cliente_id']);
            $table->unsignedBigInteger('cliente_id')->nullable(false)->change();
            $table->foreign('cliente_id')->references('id')->on('clientes');
        });
    }
};
