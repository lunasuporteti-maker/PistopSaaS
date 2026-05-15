<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->string('numero_os', 20)->unique();
            $table->foreignId('orcamento_id')->nullable()->constrained('orcamentos')->nullOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('veiculo_id')->constrained('veiculos');
            $table->text('descricao')->nullable();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->unsignedSmallInteger('garantia_dias')->default(90);
            $table->timestamp('finalizado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('numero_os');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};
