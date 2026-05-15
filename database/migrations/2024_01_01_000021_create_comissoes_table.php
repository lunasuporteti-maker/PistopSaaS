<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios');
            $table->foreignId('os_id')->constrained('ordens_servico')->cascadeOnDelete();
            $table->decimal('percentual', 5, 2)->default(0);
            $table->decimal('valor', 10, 2)->default(0);
            $table->dateTime('data_pagamento')->nullable();
            $table->boolean('pago')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissoes');
    }
};
