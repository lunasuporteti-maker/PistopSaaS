<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('marca', 50)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->unsignedSmallInteger('ano')->nullable();
            $table->string('placa', 20)->nullable()->unique();
            $table->string('cor', 30)->nullable();
            $table->unsignedInteger('km_atual')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veiculos');
    }
};
