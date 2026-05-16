<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Sincronização offline/online para o app desktop.
 *
 * Protocolo:
 *   GET  /api/sync/pull?since=TIMESTAMP  → retorna registros alterados desde o timestamp
 *   POST /api/sync/push                  → recebe registros alterados do cliente
 *   GET  /api/sync/status                → retorna timestamp do servidor
 */
class SyncController extends Controller
{
    private array $tabelas = [
        'clientes', 'veiculos', 'pecas',
        'orcamentos', 'ordens_servico',
        'agendamentos', 'funcionarios', 'parceiros',
        'mao_de_obra', 'catalogo_servicos',
    ];

    // Campos permitidos por tabela para evitar mass-assignment via sync
    private array $camposPermitidos = [
        'clientes'         => ['nome', 'telefone', 'email', 'cpf', 'endereco', 'updated_at', 'deleted_at'],
        'veiculos'         => ['cliente_id', 'marca', 'modelo', 'ano', 'placa', 'cor', 'km_atual', 'updated_at', 'deleted_at'],
        'pecas'            => ['nome', 'quantidade', 'preco_custo', 'preco_venda', 'estoque_minimo', 'updated_at', 'deleted_at'],
        'orcamentos'       => ['cliente_id', 'veiculo_id', 'status', 'observacao', 'valor_total', 'posicao_fila', 'km_entrada', 'queixa_cliente', 'parecer_tecnico', 'aprovado_em', 'iniciado_em', 'concluido_em', 'updated_at', 'deleted_at'],
        'ordens_servico'   => ['descricao', 'valor_total', 'garantia_dias', 'finalizado_em', 'updated_at', 'deleted_at'],
        'agendamentos'     => ['cliente_id', 'veiculo_id', 'data_hora', 'servico', 'status', 'observacao', 'resultado', 'updated_at', 'deleted_at'],
        'funcionarios'     => ['nome', 'cargo', 'salario_base', 'telefone', 'ativo', 'updated_at', 'deleted_at'],
        'parceiros'        => ['nome', 'servico_prestado', 'telefone', 'ativo', 'updated_at', 'deleted_at'],
        'mao_de_obra'      => ['nome', 'descricao', 'preco', 'tempo_estimado_horas', 'ativo', 'updated_at', 'deleted_at'],
        'catalogo_servicos'=> ['nome', 'descricao', 'preco_sugerido', 'tempo_estimado_horas', 'dias_lembrete', 'ativo', 'updated_at', 'deleted_at'],
    ];

    public function status()
    {
        return response()->json([
            'server_time' => now()->toIso8601String(),
            'version'     => config('app.version', '1.0.0'),
        ]);
    }

    public function pull(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $since = $request->since
            ? Carbon::parse($request->since)
            : Carbon::now()->subDays(30);

        $payload = [];

        foreach ($this->tabelas as $tabela) {
            $hasTenant = DB::getSchemaBuilder()->hasColumn($tabela, 'tenant_id');

            $query = DB::table($tabela)->where('updated_at', '>', $since);

            if ($hasTenant) {
                $query->where('tenant_id', $tenantId);
            }

            $payload[$tabela] = $query->get();
        }

        return response()->json([
            'synced_at' => now()->toIso8601String(),
            'data'      => $payload,
        ]);
    }

    public function push(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $request->validate([
            'data'        => 'required|array',
            'client_time' => 'required|string',
        ]);

        $conflicts = [];
        $saved     = 0;

        DB::beginTransaction();
        try {
            foreach ($request->data as $tabela => $registros) {
                if (! in_array($tabela, $this->tabelas)) {
                    continue;
                }

                $hasTenant        = DB::getSchemaBuilder()->hasColumn($tabela, 'tenant_id');
                $camposPermitidos = $this->camposPermitidos[$tabela] ?? [];

                foreach ($registros as $registro) {
                    $registro = (array) $registro;
                    $id       = $registro['id'] ?? null;

                    if (! $id || ! is_int($id)) {
                        continue;
                    }

                    $dadosFiltrados = array_intersect_key(
                        $registro,
                        array_flip($camposPermitidos)
                    );

                    if (empty($dadosFiltrados)) {
                        continue;
                    }

                    $query     = DB::table($tabela)->where('id', $id);
                    $existente = $hasTenant
                        ? $query->where('tenant_id', $tenantId)->first()
                        : $query->first();

                    if ($existente) {
                        // Garante que o registro pertence ao tenant antes de atualizar
                        if ($hasTenant && $existente->tenant_id !== $tenantId) {
                            continue;
                        }

                        $serverTime = Carbon::parse($existente->updated_at);
                        $clientTime = Carbon::parse($registro['updated_at'] ?? now());

                        if ($serverTime->gt($clientTime)) {
                            $conflicts[] = ['tabela' => $tabela, 'id' => $id];
                            continue;
                        }

                        DB::table($tabela)->where('id', $id)->update($dadosFiltrados);
                    } else {
                        $insert = ['id' => $id] + $dadosFiltrados;
                        if ($hasTenant) {
                            $insert['tenant_id'] = $tenantId;
                        }
                        DB::table($tabela)->insert($insert);
                    }

                    $saved++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro na sincronização.'], 500);
        }

        return response()->json([
            'saved'     => $saved,
            'conflicts' => $conflicts,
            'synced_at' => now()->toIso8601String(),
        ]);
    }
}
