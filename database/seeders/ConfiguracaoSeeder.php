<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracaoSeeder extends Seeder
{
    private array $configsPadrao = [
        ['chave' => 'google_review_link', 'valor' => '',        'descricao' => 'Link para avaliação no Google (Google Maps / Google Meu Negócio)'],
        ['chave' => 'nome_oficina',        'valor' => 'PitStop', 'descricao' => 'Nome da oficina exibido nos PDFs e mensagens'],
        ['chave' => 'telefone_oficina',    'valor' => '',        'descricao' => 'Telefone/WhatsApp da oficina'],
        ['chave' => 'endereco_oficina',    'valor' => '',        'descricao' => 'Endereço completo da oficina'],
        ['chave' => 'mensagem_review',     'valor' => 'Ficamos felizes em atender você! Poderia nos avaliar no Google? Sua opinião é muito importante para nós 🙏', 'descricao' => 'Mensagem de convite para avaliação Google'],
    ];

    public function run(): void
    {
        // Cria configurações para todos os tenants que ainda não têm
        Tenant::all()->each(function (Tenant $tenant) {
            foreach ($this->configsPadrao as $cfg) {
                $existe = DB::table('configuracoes')
                    ->where('tenant_id', $tenant->id)
                    ->where('chave', $cfg['chave'])
                    ->exists();

                if (! $existe) {
                    DB::table('configuracoes')->insert([
                        'tenant_id'  => $tenant->id,
                        'chave'      => $cfg['chave'],
                        'valor'      => $cfg['valor'],
                        'descricao'  => $cfg['descricao'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
