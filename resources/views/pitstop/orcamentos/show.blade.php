@extends('adminlte::page')
@section('title', 'Orçamento #' . $orcamento->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="m-0">Orçamento #{{ $orcamento->id }}</h1>
        <div class="d-flex flex-wrap gap-1">
            @if($orcamento->status === 'orcamento')
            <form method="POST" action="{{ route('orcamentos.aprovar', $orcamento) }}" class="d-inline">
                @csrf
                <button class="btn btn-success btn-sm" onclick="return confirm('Aprovar este orçamento?')">
                    <i class="fas fa-check"></i> Aprovar
                </button>
            </form>
            @endif
            @if(in_array($orcamento->status, ['aprovado','em_servico']) && !$orcamento->ordemServico)
            <form method="POST" action="{{ route('orcamentos.gerar-os', $orcamento) }}" class="d-inline ml-1">
                @csrf
                <button class="btn btn-warning btn-sm" onclick="return confirm('Gerar OS para este orçamento?')">
                    <i class="fas fa-tools"></i> Gerar OS
                </button>
            </form>
            @endif
            @if($orcamento->ordemServico)
            <a href="{{ route('ordens.show', $orcamento->ordemServico) }}" class="btn btn-info btn-sm ml-1">
                <i class="fas fa-eye"></i> OS {{ $orcamento->ordemServico->numero_os }}
            </a>
            @endif
            <a href="{{ route('orcamentos.edit', $orcamento) }}" class="btn btn-secondary btn-sm ml-1">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary btn-sm ml-1">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="row">
    {{-- Painel lateral: dados --}}
    <div class="col-md-4">
        <div class="card card-danger card-outline shadow-sm">
            <div class="card-header"><h3 class="card-title">Dados do Orçamento</h3></div>
            <div class="card-body">
                @php $cores = ['orcamento'=>'secondary','aprovado'=>'info','em_servico'=>'warning','concluido'=>'success','cancelado'=>'danger'] @endphp
                <span class="badge badge-{{ $cores[$orcamento->status] ?? 'secondary' }} mb-2 px-3 py-1" style="font-size:.9rem">
                    {{ ucfirst(str_replace('_',' ',$orcamento->status)) }}
                </span>
                <dl class="row mb-0 mt-2">
                    <dt class="col-5">Cliente</dt>
                    <dd class="col-7">
                        <a href="{{ route('clientes.show', $orcamento->cliente) }}">{{ $orcamento->cliente->nome }}</a>
                    </dd>
                    <dt class="col-5">Veículo</dt>
                    <dd class="col-7">
                        {{ $orcamento->veiculo->marca }} {{ $orcamento->veiculo->modelo }}
                        <br><small class="text-muted">{{ $orcamento->veiculo->placa }}</small>
                    </dd>
                    <dt class="col-5">KM Entrada</dt>
                    <dd class="col-7">{{ $orcamento->km_entrada ? number_format($orcamento->km_entrada) . ' km' : '—' }}</dd>
                    <dt class="col-5">Queixa</dt>
                    <dd class="col-7">{{ $orcamento->queixa_cliente ?? '—' }}</dd>
                    <dt class="col-5">Parecer</dt>
                    <dd class="col-7">{{ $orcamento->parecer_tecnico ?? '—' }}</dd>
                    <dt class="col-5">Observação</dt>
                    <dd class="col-7">{{ $orcamento->observacao ?? '—' }}</dd>
                    <dt class="col-5">Criado em</dt>
                    <dd class="col-7">{{ $orcamento->created_at->format('d/m/Y H:i') }}</dd>
                </dl>
                <hr>
                <h4 class="text-right mb-0">
                    Total: <strong class="text-danger">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</strong>
                </h4>
            </div>
        </div>
    </div>

    {{-- Painel direito: itens --}}
    <div class="col-md-8">

        {{-- Serviços --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-wrench mr-1"></i> Serviços</h3>
                @if(in_array($orcamento->status, ['orcamento','aprovado','em_servico']))
                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalServico">
                    <i class="fas fa-plus"></i> Adicionar Serviço
                </button>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Descrição</th><th width="120">Valor</th><th width="50"></th></tr>
                    </thead>
                    <tbody>
                        @forelse($orcamento->servicos as $s)
                        <tr>
                            <td>{{ $s->servico_nome }}</td>
                            <td>R$ {{ number_format($s->valor, 2, ',', '.') }}</td>
                            <td>
                                <form method="POST" action="{{ route('orcamentos.servicos.remove', [$orcamento, $s]) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" onclick="return confirm('Remover este serviço?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">
                                <i class="fas fa-info-circle mr-1"></i> Nenhum serviço adicionado.
                                @if(in_array($orcamento->status, ['orcamento','aprovado','em_servico']))
                                <br><small>Clique em "Adicionar Serviço" para começar.</small>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Peças --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-boxes mr-1"></i> Peças</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Peça</th><th>Qtd</th><th>Unit.</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse($orcamento->pecas as $p)
                        <tr>
                            <td>{{ $p->peca->nome }}</td>
                            <td>{{ $p->quantidade }}</td>
                            <td>R$ {{ number_format($p->preco_unitario, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($p->quantidade * $p->preco_unitario, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-2">Nenhuma peça.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mão de Obra --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-user-cog mr-1"></i> Mão de Obra</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Descrição</th><th width="120">Valor</th></tr>
                    </thead>
                    <tbody>
                        @forelse($orcamento->maoDeObra as $m)
                        <tr>
                            <td>{{ $m->nome_custom ?? $m->maoDeObra?->nome }}</td>
                            <td>R$ {{ number_format($m->valor, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-2">Nenhum item de mão de obra.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- Modal: Adicionar Serviço (#15) --}}
<div class="modal fade" id="modalServico" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-wrench mr-2"></i>Adicionar Serviço</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="{{ route('orcamentos.servicos.add', $orcamento) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Descrição do Serviço <span class="text-danger">*</span></label>
                        <input type="text" name="servico_nome" class="form-control form-control-lg"
                               placeholder="EX: TROCA DE ÓLEO, ALINHAMENTO..."
                               maxlength="200" data-uppercase required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Valor (R$) <span class="text-danger">*</span></label>
                        <input type="number" name="valor" class="form-control form-control-lg"
                               placeholder="0.00" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-plus mr-1"></i> Adicionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
