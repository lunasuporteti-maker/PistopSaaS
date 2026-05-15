<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_km', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->unsignedInteger('km');
            $table->string('observacao', 200)->nullable();
            $table->timestamps();

            $table->index('veiculo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_km');
    }
};
