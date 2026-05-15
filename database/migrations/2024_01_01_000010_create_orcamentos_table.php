<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('veiculo_id')->constrained('veiculos');
            // orcamento | aprovado | em_servico | concluido | cancelado
            $table->string('status', 30)->default('orcamento');
            $table->text('observacao')->nullable();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->unsignedInteger('posicao_fila')->nullable();
            $table->unsignedInteger('km_entrada')->nullable();
            $table->text('queixa_cliente')->nullable();
            $table->text('parecer_tecnico')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('posicao_fila');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamentos');
    }
};
