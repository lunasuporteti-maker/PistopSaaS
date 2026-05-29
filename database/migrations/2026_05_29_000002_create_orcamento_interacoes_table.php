<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamento_interacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('orcamento_id')->index();

            // Tipo lógico (string + constantes no Model — portável SQLite/PG, extensível):
            // visualizacao | aprovacao | rejeicao | revisao_valor | upload_foto | exclusao_foto
            $table->string('tipo', 30);

            // dados_json como text (portável; cast 'array' no Model). NÃO usar jsonb.
            $table->text('dados_json')->nullable();

            // Cliente externo (portal) → usuario_id NULL; ação interna → id do user
            $table->unsignedBigInteger('usuario_id')->nullable();

            // Insert-only / evidência legal imutável: apenas created_at, sem updated_at
            $table->timestamp('created_at')->useCurrent();

            // FKs
            $table->foreign('orcamento_id')->references('id')->on('orcamentos')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->nullOnDelete();

            // Índices
            $table->index(['tenant_id', 'orcamento_id']);
            $table->index(['orcamento_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamento_interacoes');
    }
};
