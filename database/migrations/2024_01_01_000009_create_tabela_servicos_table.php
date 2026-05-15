<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabela_servicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->decimal('tempo_estimado_horas', 4, 2)->nullable();
            $table->decimal('preco_mao_de_obra', 10, 2)->nullable();
            $table->decimal('margem_lucro_percent', 5, 2)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabela_servicos');
    }
};
