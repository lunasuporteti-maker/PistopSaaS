<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave', 80)->unique();
            $table->text('valor')->nullable();
            $table->string('descricao', 200)->nullable();
            $table->timestamps();
        });

        // Valores padrão
        DB::table('configuracoes')->insert([
            ['chave' => 'google_review_link', 'valor' => '', 'descricao' => 'Link para avaliação no Google (Google Maps / Google Meu Negócio)', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'nome_oficina',        'valor' => 'PitStop',     'descricao' => 'Nome da oficina exibido nos PDFs e mensagens', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'telefone_oficina',    'valor' => '',            'descricao' => 'Telefone/WhatsApp da oficina', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'endereco_oficina',    'valor' => '',            'descricao' => 'Endereço completo da oficina', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'mensagem_review',     'valor' => 'Ficamos felizes em atender você! Poderia nos avaliar no Google? Sua opinião é muito importante para nós 🙏', 'descricao' => 'Mensagem de convite para avaliação Google', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes');
    }
};
