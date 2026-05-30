@extends('layouts.pitstop')
@section('title', 'Comissões')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold"><i class="fas fa-hand-holding-usd mr-2 text-success"></i>Comissões</h1>
    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalNovaComissao">
        <i class="fas fa-plus mr-1"></i> Nova Comissão
    </button>
</div>
@endsection

@section('content')

{{-- Filtros --}}
<form method="GET" class="card shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap align-items-end" style="gap:12px">
        <div>
            <label class="form-label small mb-1">Mês</label>
            <input type="month" name="mes" value="{{ $mes }}" class="form-control form-control-sm">
        </div>
        <div>
            <label class="form-label small mb-1">Funcionário</label>
            <select name="funcionario_id" class="form-control form-control-sm" style="min-width:180px">
                <option value="">Todos</option>
                @foreach($funcionarios as $f)
                <option value="{{ $f->id }}" {{ $funcionarioId == $f->id ? 'selected' : '' }}>{{ $f->nome }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
    </div>
</form>

{{-- Totais --}}
<div class="row mb-4">
    <div class="col-6 col-md-4 mb-3">
        <div class="card shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Total a pagar</div>
            <div class="font-weight-bold" style="font-size:1.2rem;color:#27ae60">
                R$ {{ number_format($totais->total ?? 0, 2, ',', '.') }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="card shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Já pago</div>
            <div class="font-weight-bold" style="font-size:1.2rem;color:#2980b9">
                R$ {{ number_format($totais->total_pago ?? 0, 2, ',', '.') }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="card shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Pendente</div>
            @php $pendente = ($totais->total ?? 0) - ($totais->total_pago ?? 0); @endphp
            <div class="font-weight-bold" style="font-size:1.2rem;color:{{ $pendente > 0 ? '#e67e22' : '#27ae60' }}">
                R$ {{ number_format($pendente, 2, ',', '.') }}
            </div>
        </div>
    </div>
</div>

{{-- Tabela --}}
<div class="card shadow-sm">
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th class="pl-3">Funcionário</th>
                    <th>OS</th>
                    <th>Cliente</th>
                    <th class="text-right">%</th>
                    <th class="text-right">Valor</th>
                    <th>Status</th>
                    <th>Pago em</th>
                    <th class="text-right pr-3">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comissoes as $c)
                <tr>
                    <td class="pl-3 font-weight-600">{{ $c->funcionario->nome }}</td>
                    <td>
                        @if($c->ordemServico)
                        <a href="{{ route('ordens.show', $c->ordemServico) }}">{{ $c->ordemServico->numero_os }}</a>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $c->ordemServico?->cliente?->nome ?? '—' }}</td>
                    <td class="text-right">{{ $c->percentual ? $c->percentual . '%' : '—' }}</td>
                    <td class="text-right font-weight-bold">R$ {{ number_format($c->valor, 2, ',', '.') }}</td>
                    <td>
                        @if($c->pago)
                        <span class="badge badge-success">Pago</span>
                        @else
                        <span class="badge badge-warning">Pendente</span>
                        @endif
                    </td>
                    <td class="text-muted" style="font-size:.82rem">
                        {{ $c->data_pagamento ? $c->data_pagamento->format('d/m/Y') : '—' }}
                    </td>
                    <td class="text-right pr-3" style="white-space:nowrap">
                        @if(!$c->pago)
                        <form method="POST" action="{{ route('comissoes.pagar', $c) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button class="btn btn-xs btn-success" title="Marcar como pago">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('comissoes.destroy', $c) }}" class="d-inline"
                              onsubmit="return confirm('Remover esta comissão?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger" title="Remover">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Nenhuma comissão no período.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($comissoes->hasPages())
    <div class="card-footer">{{ $comissoes->links() }}</div>
    @endif
</div>

{{-- Modal nova comissão --}}
<div class="modal fade" id="modalNovaComissao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('comissoes.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Nova Comissão</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Funcionário <span class="text-danger">*</span></label>
                        <select name="funcionario_id" class="form-control" required>
                            <option value="">Selecione...</option>
                            @foreach($funcionarios as $f)
                            <option value="{{ $f->id }}">{{ $f->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>OS Relacionada <small class="text-muted">(opcional)</small></label>
                        <select name="os_id" class="form-control">
                            <option value="">— Nenhuma —</option>
                            @foreach($ordens as $os)
                            <option value="{{ $os->id }}">
                                {{ $os->numero_os }} — {{ $os->cliente?->nome }} ({{ $os->finalizado_em->format('d/m/Y') }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Percentual <small class="text-muted">(%)</small></label>
                                <input type="number" name="percentual" class="form-control" min="0" max="100" step="0.1" placeholder="ex: 10">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Valor (R$) <span class="text-danger">*</span></label>
                                <input type="number" name="valor" class="form-control" min="0.01" step="0.01" required placeholder="ex: 50,00">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
