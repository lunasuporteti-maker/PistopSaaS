<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            // slug = subdomínio (ex: pitstop, oficinajose)
            $table->string('slug', 60)->unique();
            // domínio customizado opcional (ex: sistema.oficinajose.com.br)
            $table->string('dominio_customizado', 200)->nullable()->unique();
            $table->string('plano', 30)->default('basico'); // basico | profissional | enterprise
            $table->boolean('ativo')->default(true);
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('dominio_customizado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
