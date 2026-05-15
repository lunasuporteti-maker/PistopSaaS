<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financeiro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_id')->nullable()->constrained('ordens_servico')->nullOnDelete();
            // entrada | saida
            $table->string('tipo', 10);
            $table->string('descricao', 200)->nullable();
            $table->decimal('valor', 10, 2);
            $table->dateTime('data_pagamento');
            $table->timestamps();

            $table->index(['tipo', 'data_pagamento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financeiro');
    }
};
