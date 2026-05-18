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
            <a href="{{ route('orcamentos.pdf', $orcamento) }}" class="btn btn-outline-danger btn-sm ml-1" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            @if($orcamento->cliente->telefone)
            @php
                $pdfPublico = route('orcamentos.pdf.publico', $orcamento->token_publico);
                $waTextOrc  = urlencode("Olá {$orcamento->cliente->nome}! Segue o link para baixar seu orçamento #{$orcamento->id} da AutoFix:\n" . $pdfPublico);
                $waOrcUrl   = 'https://wa.me/55' . preg_replace('/\D/', '', $orcamento->cliente->telefone) . '?text=' . $waTextOrc;
            @endphp
            <a href="{{ $waOrcUrl }}" target="_blank" class="btn btn-success btn-sm ml-1">
                <i class="fab fa-whatsapp"></i> Enviar WhatsApp
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
                                @if(in_array($orcamento->status, ['orcamento','aprovado','em_servico']))
                                <form method="POST" action="{{ route('orcamentos.servicos.remove', [$orcamento, $s]) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" onclick="return confirm('Remover este serviço?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                @endif
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-boxes mr-1"></i> Peças</h3>
                @if(in_array($orcamento->status, ['orcamento','aprovado','em_servico']))
                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalPeca">
                    <i class="fas fa-plus"></i> Adicionar Peça
                </button>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Peça</th><th>Qtd</th><th>Unit.</th><th>Total</th><th width="50"></th></tr>
                    </thead>
                    <tbody>
                        @forelse($orcamento->pecas as $p)
                        <tr>
                            <td>{{ $p->peca->nome }}</td>
                            <td>{{ $p->quantidade }}</td>
                            <td>R$ {{ number_format($p->preco_unitario, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($p->quantidade * $p->preco_unitario, 2, ',', '.') }}</td>
                            <td>
                                @if(in_array($orcamento->status, ['orcamento','aprovado','em_servico']))
                                <form method="POST" action="{{ route('orcamentos.pecas.remove', [$orcamento, $p]) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" onclick="return confirm('Remover esta peça?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                Nenhuma peça adicionada.
                                @if(in_array($orcamento->status, ['orcamento','aprovado','em_servico']))
                                <br><small>Clique em "Adicionar Peça" para incluir peças do estoque.</small>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mão de Obra --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-user-cog mr-1"></i> Mão de Obra</h3>
                @if(in_array($orcamento->status, ['orcamento','aprovado','em_servico']))
                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalMdo">
                    <i class="fas fa-plus"></i> Adicionar Mão de Obra
                </button>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Descrição</th><th width="120">Valor</th><th width="50"></th></tr>
                    </thead>
                    <tbody>
                        @forelse($orcamento->maoDeObra as $m)
                        <tr>
                            <td>{{ $m->nome_custom ?? $m->maoDeObra?->nome }}</td>
                            <td>R$ {{ number_format($m->valor, 2, ',', '.') }}</td>
                            <td>
                                @if(in_array($orcamento->status, ['orcamento','aprovado','em_servico']))
                                <form method="POST" action="{{ route('orcamentos.mdo.remove', [$orcamento, $m]) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" onclick="return confirm('Remover este item?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">
                                Nenhum item de mão de obra.
                                @if(in_array($orcamento->status, ['orcamento','aprovado','em_servico']))
                                <br><small>Clique em "Adicionar Mão de Obra" para incluir.</small>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- Modal: Adicionar Serviço --}}
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
                    @if($catalogoServicos->isNotEmpty())
                    <div class="form-group">
                        <label>Selecionar do Catálogo</label>
                        <select id="selectCatalogo" class="form-control">
                            <option value="">— Selecione ou preencha manualmente —</option>
                            @foreach($catalogoServicos as $cs)
                            <option value="{{ $cs->nome }}" data-preco="{{ $cs->preco_sugerido ?? '' }}">
                                {{ $cs->nome }}{{ $cs->preco_sugerido ? ' — R$ ' . number_format($cs->preco_sugerido, 2, ',', '.') : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group">
                        <label>Descrição do Serviço <span class="text-danger">*</span></label>
                        <input type="text" name="servico_nome" id="inputServicoNome" class="form-control form-control-lg"
                               placeholder="EX: TROCA DE ÓLEO, ALINHAMENTO..."
                               maxlength="200" data-uppercase required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Valor (R$) <span class="text-danger">*</span></label>
                        <input type="number" name="valor" id="inputServicoValor" class="form-control form-control-lg"
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

{{-- Modal: Adicionar Peça --}}
<div class="modal fade" id="modalPeca" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-boxes mr-2"></i>Adicionar Peça</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="{{ route('orcamentos.pecas.add', $orcamento) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Peça <span class="text-danger">*</span></label>
                        <select name="peca_id" id="selectPeca" class="form-control form-control-lg" required>
                            <option value="">— Selecione uma peça —</option>
                            @foreach($pecas as $pc)
                            <option value="{{ $pc->id }}"
                                    data-preco="{{ $pc->preco_venda ?? 0 }}"
                                    data-estoque="{{ $pc->quantidade }}">
                                {{ $pc->nome }}
                                (Estoque: {{ $pc->quantidade }})
                                {{ $pc->preco_venda ? '— R$ ' . number_format($pc->preco_venda, 2, ',', '.') : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-5">
                            <div class="form-group">
                                <label>Quantidade <span class="text-danger">*</span></label>
                                <input type="number" name="quantidade" id="inputPecaQtd"
                                       class="form-control form-control-lg" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="form-group">
                                <label>Valor Unitário (R$) <span class="text-danger">*</span></label>
                                <input type="number" name="preco_unitario" id="inputPecaPreco"
                                       class="form-control form-control-lg" placeholder="0.00" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">O valor unitário é preenchido automaticamente pelo preço de venda cadastrado na peça, mas pode ser editado.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="fas fa-plus mr-1"></i> Adicionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Adicionar Mão de Obra --}}
<div class="modal fade" id="modalMdo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-user-cog mr-2"></i>Adicionar Mão de Obra</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="{{ route('orcamentos.mdo.add', $orcamento) }}">
                @csrf
                <div class="modal-body">
                    @if($maos->isNotEmpty())
                    <div class="form-group">
                        <label>Selecionar do Catálogo</label>
                        <select id="selectMdo" name="mao_de_obra_id" class="form-control">
                            <option value="">— Personalizado (preencher manualmente) —</option>
                            @foreach($maos as $mdo)
                            <option value="{{ $mdo->id }}" data-nome="{{ $mdo->nome }}" data-preco="{{ $mdo->preco }}">
                                {{ $mdo->nome }} — R$ {{ number_format($mdo->preco, 2, ',', '.') }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group" id="grupNomeCustom">
                        <label>Descrição <span class="text-danger">*</span> <small class="text-muted">(quando personalizado)</small></label>
                        <input type="text" name="nome_custom" id="inputMdoNome" class="form-control"
                               placeholder="EX: DIAGNÓSTICO ELETRÔNICO..." maxlength="200" data-uppercase>
                    </div>
                    <div class="form-group">
                        <label>Valor (R$) <span class="text-danger">*</span></label>
                        <input type="number" name="valor" id="inputMdoValor"
                               class="form-control form-control-lg" placeholder="0.00" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="fas fa-plus mr-1"></i> Adicionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
// Catálogo de serviços → preenche nome e valor
document.getElementById('selectCatalogo')?.addEventListener('change', function () {
    var opt = this.options[this.selectedIndex];
    if (!opt.value) return;
    document.getElementById('inputServicoNome').value = opt.value.toUpperCase();
    var preco = opt.dataset.preco;
    if (preco) document.getElementById('inputServicoValor').value = parseFloat(preco).toFixed(2);
});

// Peças → preenche preço ao selecionar
document.getElementById('selectPeca')?.addEventListener('change', function () {
    var opt = this.options[this.selectedIndex];
    var preco = parseFloat(opt.dataset.preco || 0);
    document.getElementById('inputPecaPreco').value = preco > 0 ? preco.toFixed(2) : '';
});

// Mão de obra catálogo → preenche nome e valor; esconde campo nome quando selecionado do catálogo
var selectMdo = document.getElementById('selectMdo');
var grupNome  = document.getElementById('grupNomeCustom');
if (selectMdo) {
    selectMdo.addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        if (opt.value) {
            // item do catálogo
            document.getElementById('inputMdoNome').value = '';
            document.getElementById('inputMdoValor').value = parseFloat(opt.dataset.preco || 0).toFixed(2);
            grupNome.style.display = 'none';
            document.getElementById('inputMdoNome').removeAttribute('required');
        } else {
            // personalizado
            document.getElementById('inputMdoNome').value = '';
            document.getElementById('inputMdoValor').value = '';
            grupNome.style.display = '';
            document.getElementById('inputMdoNome').setAttribute('required', 'required');
        }
    });
}
</script>
@endpush
@endsection
