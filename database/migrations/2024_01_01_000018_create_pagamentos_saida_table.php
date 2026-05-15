<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos_saida', function (Blueprint $table) {
            $table->id();
            // salario | comissao | fornecedor | aluguel | manutencao | outros
            $table->string('tipo', 30);
            $table->string('descricao', 200)->nullable();
            $table->decimal('valor', 10, 2);
            $table->foreignId('funcionario_id')->nullable()->constrained('funcionarios')->nullOnDelete();
            $table->foreignId('parceiro_id')->nullable()->constrained('parceiros')->nullOnDelete();
            $table->dateTime('data_pagamento');
            $table->string('mes_referencia', 7)->nullable();
            $table->string('categoria', 30)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('data_pagamento');
            $table->index('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos_saida');
    }
};
