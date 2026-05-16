@extends('adminlte::page')
@section('title', 'Financeiro')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 font-weight-bold"><i class="fas fa-cash-register mr-2 text-danger"></i>Lançamentos</h1>
        <small class="text-muted">Saídas de Caixa</small>
    </div>
    <div class="d-flex align-items-center gap-2" style="gap:8px">
        <form method="GET" class="d-flex align-items-center" style="gap:6px">
            <div class="input-group input-group-sm" style="max-width:160px">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-calendar text-muted"></i></span>
                </div>
                <input type="month" name="mes" class="form-control" value="{{ $mes }}">
            </div>
            <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
        </form>
    </div>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="row">
    {{-- Tabela de saídas --}}
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <span class="font-weight-600" style="font-size:.9rem">
                    <i class="fas fa-arrow-down mr-2 text-danger"></i>Saídas do Mês
                </span>
                <span class="font-weight-700 text-danger" style="font-size:.95rem">
                    Total: R$ {{ number_format($saidas->sum('valor'), 2, ',', '.') }}
                </span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="pl-3">Data</th>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th>Categoria</th>
                            <th>Valor</th>
                            <th class="pr-3" width="50"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($saidas as $s)
                        @php
                        $catColors = [
                            'pecas'=>'badge-info','salario'=>'badge-success','aluguel'=>'badge-primary',
                            'fornecedor'=>'badge-warning','servico'=>'badge-secondary','outros'=>'badge-secondary'
                        ];
                        $catLabel = [
                            'pecas'=>'Peças','salario'=>'Salário','aluguel'=>'Aluguel',
                            'fornecedor'=>'Fornecedor','servico'=>'Serviço','outros'=>'Outros'
                        ];
                        @endphp
                        <tr>
                            <td class="pl-3 text-muted">{{ \Carbon\Carbon::parse($s->data_pagamento)->format('d/m') }}</td>
                            <td>{{ ucfirst($s->tipo) }}</td>
                            <td>{{ $s->descricao ?? ($s->funcionario?->nome ?? $s->parceiro?->nome ?? '—') }}</td>
                            <td>
                                <span class="badge {{ $catColors[$s->categoria] ?? 'badge-secondary' }}">
                                    {{ $catLabel[$s->categoria] ?? ucfirst($s->categoria ?? 'Outros') }}
                                </span>
                            </td>
                            <td class="font-weight-600 text-danger">R$ {{ number_format($s->valor, 2, ',', '.') }}</td>
                            <td class="pr-3" style="white-space:nowrap">
                                <button class="btn btn-xs btn-outline-secondary btn-editar-lancamento" title="Editar"
                                    data-id="{{ $s->id }}"
                                    data-tipo="{{ $s->tipo }}"
                                    data-descricao="{{ $s->descricao }}"
                                    data-valor="{{ $s->valor }}"
                                    data-data="{{ $s->data_pagamento }}"
                                    data-categoria="{{ $s->categoria }}"
                                    data-funcionario="{{ $s->funcionario_id }}"
                                    data-parceiro="{{ $s->parceiro_id }}"
                                    data-action="{{ route('financeiro.update', $s) }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('financeiro.destroy', $s) }}" class="d-inline"
                                      onsubmit="return confirm('Excluir lançamento?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" title="Excluir"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-receipt fa-2x d-block mb-2 text-light"></i>
                                Nenhum lançamento neste mês.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($saidas->hasPages())
            <div class="card-footer">{{ $saidas->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Formulário de novo lançamento --}}
    <div class="col-md-4">
        <div class="card card-outline card-danger shadow-sm" style="position:sticky;top:10px">
            <div class="card-header py-2">
                <span class="font-weight-600" style="font-size:.9rem">
                    <i class="fas fa-plus-circle mr-2 text-danger"></i>Novo Lançamento
                </span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('financeiro.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-control form-control-sm" required>
                            <option value="">Selecione...</option>
                            <option value="salario">Salário</option>
                            <option value="comissao">Comissão</option>
                            <option value="fornecedor">Fornecedor</option>
                            <option value="aluguel">Aluguel</option>
                            <option value="manutencao">Manutenção</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="categoria" class="form-control form-control-sm">
                            <option value="outros">Outros</option>
                            <option value="pecas">Peças / Estoque</option>
                            <option value="salario">Salários</option>
                            <option value="aluguel">Aluguel</option>
                            <option value="fornecedor">Fornecedor</option>
                            <option value="servico">Serviços Terceiros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Descrição</label>
                        <input type="text" name="descricao" class="form-control form-control-sm" maxlength="150">
                    </div>
                    <div class="form-group">
                        <label>Funcionário</label>
                        <select name="funcionario_id" class="form-control form-control-sm">
                            <option value="">—</option>
                            @foreach($funcionarios as $f)
                            <option value="{{ $f->id }}">{{ $f->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Parceiro</label>
                        <select name="parceiro_id" class="form-control form-control-sm">
                            <option value="">—</option>
                            @foreach($parceiros as $p)
                            <option value="{{ $p->id }}">{{ $p->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Valor <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="valor"
                                       class="form-control form-control-sm" required min="0.01">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Data <span class="text-danger">*</span></label>
                                <input type="date" name="data_pagamento"
                                       class="form-control form-control-sm"
                                       value="{{ today()->toDateString() }}" required>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="mes_referencia" value="{{ $mes }}">
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fas fa-save mr-1"></i> Registrar Saída
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Editar Lançamento (#42) --}}
<div class="modal fade" id="modalEditarLancamento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Lançamento</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formEditarLancamento" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" id="edit_tipo" class="form-control form-control-sm" required>
                            <option value="salario">Salário</option>
                            <option value="comissao">Comissão</option>
                            <option value="fornecedor">Fornecedor</option>
                            <option value="aluguel">Aluguel</option>
                            <option value="manutencao">Manutenção</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="categoria" id="edit_categoria" class="form-control form-control-sm">
                            <option value="outros">Outros</option>
                            <option value="pecas">Peças / Estoque</option>
                            <option value="salario">Salários</option>
                            <option value="aluguel">Aluguel</option>
                            <option value="fornecedor">Fornecedor</option>
                            <option value="servico">Serviços Terceiros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Descrição</label>
                        <input type="text" name="descricao" id="edit_descricao" class="form-control form-control-sm" maxlength="150">
                    </div>
                    <div class="form-group">
                        <label>Funcionário</label>
                        <select name="funcionario_id" id="edit_funcionario" class="form-control form-control-sm">
                            <option value="">—</option>
                            @foreach($funcionarios as $f)
                            <option value="{{ $f->id }}">{{ $f->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Parceiro</label>
                        <select name="parceiro_id" id="edit_parceiro" class="form-control form-control-sm">
                            <option value="">—</option>
                            @foreach($parceiros as $p)
                            <option value="{{ $p->id }}">{{ $p->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Valor <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="valor" id="edit_valor"
                                       class="form-control form-control-sm" required min="0.01">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Data <span class="text-danger">*</span></label>
                                <input type="date" name="data_pagamento" id="edit_data"
                                       class="form-control form-control-sm" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-save mr-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
document.querySelectorAll('.btn-editar-lancamento').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var f = document.getElementById('formEditarLancamento');
        f.action = this.dataset.action;
        document.getElementById('edit_tipo').value        = this.dataset.tipo || '';
        document.getElementById('edit_categoria').value   = this.dataset.categoria || 'outros';
        document.getElementById('edit_descricao').value   = this.dataset.descricao || '';
        document.getElementById('edit_valor').value       = this.dataset.valor || '';
        document.getElementById('edit_data').value        = this.dataset.data || '';
        document.getElementById('edit_funcionario').value = this.dataset.funcionario || '';
        document.getElementById('edit_parceiro').value    = this.dataset.parceiro || '';
        $('#modalEditarLancamento').modal('show');
    });
});
</script>
@endpush

@endsection
