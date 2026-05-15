<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave', 80)->unique();
            $table->text('valor')->nullable();
            $table->string('descricao', 200)->nullable();
            $table->timestamps();
        });

        // Configurações padrão são criadas via ConfiguracaoSeeder (por tenant)
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes');
    }
};
