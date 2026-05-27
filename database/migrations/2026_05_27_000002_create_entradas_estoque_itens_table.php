<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entradas_estoque_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            $table->unsignedBigInteger('entrada_id');
            $table->unsignedBigInteger('peca_id');

            $table->unsignedInteger('quantidade');
            $table->decimal('preco_custo_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->timestamps();

            // FKs
            $table->foreign('entrada_id')
                  ->references('id')->on('entradas_estoque')
                  ->onDelete('cascade');   // itens são excluídos junto com a entrada

            $table->foreign('peca_id')
                  ->references('id')->on('pecas')
                  ->onDelete('restrict');  // não excluir peça se tiver entrada vinculada

            // Índices
            $table->index(['tenant_id', 'entrada_id']);
            $table->index(['tenant_id', 'peca_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entradas_estoque_itens');
    }
};
