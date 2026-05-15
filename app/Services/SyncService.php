<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Gerencia a sincronização entre o app desktop (SQLite offline) e o servidor (MySQL).
 *
 * Estratégia: last-write-wins com detecção de conflito por timestamp.
 * Quando updated_at do servidor > updated_at do cliente, o registro é marcado
 * como conflito e NÃO é sobrescrito — cabe ao cliente resolver.
 */
class SyncService
{
    private array $tabelasPermitidas = [
        'clientes', 'veiculos', 'pecas',
        'orcamentos', 'ordens_servico',
        'agendamentos', 'funcionarios', 'parceiros',
        'mao_de_obra', 'catalogo_servicos',
    ];

    public function pull(Carbon $since): array
    {
        $data = [];

        foreach ($this->tabelasPermitidas as $tabela) {
            $data[$tabela] = DB::table($tabela)
                ->where('updated_at', '>', $since)
                ->get()
                ->toArray();
        }

        return $data;
    }

    public function push(array $payload): array
    {
        $saved     = 0;
        $skipped   = 0;
        $conflicts = [];

        foreach ($payload as $tabela => $registros) {
            if (! in_array($tabela, $this->tabelasPermitidas)) {
                continue;
            }

            foreach ($registros as $registro) {
                $registro = (array) $registro;
                $id       = $registro['id'] ?? null;

                if (! $id) {
                    DB::table($tabela)->insert($registro);
                    $saved++;
                    continue;
                }

                $existente = DB::table($tabela)->where('id', $id)->first();

                if (! $existente) {
                    DB::table($tabela)->insert($registro);
                    $saved++;
                    continue;
                }

                $serverTs = Carbon::parse($existente->updated_at);
                $clientTs = Carbon::parse($registro['updated_at'] ?? Carbon::now());

                if ($serverTs->lte($clientTs)) {
                    unset($registro['id'], $registro['created_at']);
                    DB::table($tabela)->where('id', $id)->update($registro);
                    $saved++;
                } else {
                    $conflicts[] = [
                        'tabela'       => $tabela,
                        'id'           => $id,
                        'server_data'  => $existente,
                        'client_data'  => $registro,
                    ];
                    $skipped++;
                }
            }
        }

        return compact('saved', 'skipped', 'conflicts');
    }
}
