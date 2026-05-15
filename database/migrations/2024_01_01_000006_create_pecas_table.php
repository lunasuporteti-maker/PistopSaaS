<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pecas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200);
            $table->unsignedInteger('quantidade')->default(0);
            $table->decimal('preco_custo', 10, 2)->nullable();
            $table->decimal('preco_venda', 10, 2)->nullable();
            $table->unsignedInteger('estoque_minimo')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pecas');
    }
};
