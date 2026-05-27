<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entradas_estoque', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            // Identificação
            $table->string('numero_entrada', 20);   // gerado pelo service: ENT-{ano}-{seq}
            $table->unsignedBigInteger('fornecedor_id');
            $table->date('data_entrada');

            // Documento fiscal
            $table->string('numero_nota', 100)->nullable();
            $table->enum('tipo_documento', ['nota_manual', 'cupom', 'nfe', 'sem_documento'])
                  ->default('nota_manual');

            // Financeiro
            $table->decimal('valor_total', 10, 2)->default(0);

            // Status
            $table->enum('status', ['ativa', 'cancelada'])->default('ativa');

            // Metadados
            $table->text('observacoes')->nullable();
            $table->string('anexo_path', 500)->nullable();

            // Cancelamento
            $table->unsignedBigInteger('cancelado_por')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->text('cancelado_motivo')->nullable();

            // Auditoria
            $table->unsignedBigInteger('usuario_id');

            $table->timestamps();

            // FKs
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onDelete('restrict');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('cancelado_por')->references('id')->on('users')->onDelete('set null');

            // Índices
            $table->unique(['tenant_id', 'numero_entrada']);
            $table->index(['tenant_id', 'data_entrada']);
            $table->index(['tenant_id', 'fornecedor_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entradas_estoque');
    }
};
