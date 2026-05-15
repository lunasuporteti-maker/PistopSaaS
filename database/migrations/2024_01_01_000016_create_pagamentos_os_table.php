<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos_os', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_id')->constrained('ordens_servico')->cascadeOnDelete();
            // dinheiro | cartao_credito | cartao_debito | pix | transferencia | boleto
            $table->string('forma', 30);
            $table->decimal('valor', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos_os');
    }
};
