<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->nullOnDelete();
            $table->dateTime('data_hora');
            $table->string('servico', 200)->nullable();
            // agendado | confirmado | realizado | cancelado
            $table->string('status', 20)->default('agendado');
            $table->text('observacao')->nullable();
            $table->text('resultado')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('data_hora');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
