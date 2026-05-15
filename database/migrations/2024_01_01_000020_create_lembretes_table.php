<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lembretes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->nullOnDelete();
            $table->foreignId('os_id')->nullable()->constrained('ordens_servico')->nullOnDelete();
            $table->string('servico_nome', 200);
            $table->dateTime('data_servico');
            $table->date('data_lembrete');
            // pendente | enviado | cancelado
            $table->string('status', 20)->default('pendente');
            $table->timestamps();

            $table->index('data_lembrete');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembretes');
    }
};
