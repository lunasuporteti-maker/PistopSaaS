<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servico_fotos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('orcamento_id')->index();
            $table->unsignedBigInteger('ordem_servico_id')->nullable();

            // Categoria lógica (string + constantes no Model):
            // antes | durante | depois | peca | outro
            $table->string('categoria', 20);
            $table->string('legenda', 200)->nullable();

            $table->string('path_original');
            $table->string('path_thumbnail')->nullable();
            $table->integer('tamanho_bytes');
            $table->string('mime_type', 50);

            $table->unsignedBigInteger('uploaded_by');

            $table->timestamps();
            $table->softDeletes();

            // FKs
            $table->foreign('orcamento_id')->references('id')->on('orcamentos')->cascadeOnDelete();
            $table->foreign('ordem_servico_id')->references('id')->on('ordens_servico')->nullOnDelete();
            // restrictOnDelete: não apagar foto (nem o user) acidentalmente
            $table->foreign('uploaded_by')->references('id')->on('users')->restrictOnDelete();

            // Índices
            $table->index(['tenant_id', 'orcamento_id']);
            $table->index(['orcamento_id', 'categoria']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servico_fotos');
    }
};
