<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caixas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->date('data');
            $table->decimal('saldo_inicial', 10, 2)->default(0);
            $table->decimal('saldo_final', 10, 2)->nullable();
            $table->text('observacao_abertura')->nullable();
            $table->text('observacao_fechamento')->nullable();
            $table->enum('status', ['aberto', 'fechado'])->default('aberto');
            $table->timestamp('aberto_em')->useCurrent();
            $table->timestamp('fechado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caixas');
    }
};
