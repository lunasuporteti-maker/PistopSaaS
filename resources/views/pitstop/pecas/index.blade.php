@extends('layouts.pitstop')
@section('title', 'Estoque de Peças')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 font-weight-bold"><i class="fas fa-boxes mr-2 text-danger"></i>Estoque de Peças</h1>
        <small class="text-muted">{{ $pecas->total() }} peça(s) cadastrada(s)</small>
    </div>
    <a href="{{ route('pecas.create') }}" class="btn btn-danger btn-sm px-3">
        <i class="fas fa-plus mr-1"></i> Nova Peça
    </a>
</div>
@endsection

@section('content')

<div class="card shadow-sm">
    <div class="card-header py-2">
        <form method="GET" class="d-flex align-items-center flex-wrap" style="gap:8px">
            <div class="input-group input-group-sm" style="max-width:280px">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="text" name="search" class="form-control"
                       placeholder="Nome da peça..."
                       value="{{ request('search') }}">
            </div>
            <button class="btn btn-sm btn-danger">Buscar</button>
            <label class="d-flex align-items-center mb-0 ml-2" style="gap:6px;cursor:pointer">
                <input type="checkbox" name="estoque_baixo" value="1"
                       {{ request('estoque_baixo') ? 'checked' : '' }}
                       onchange="this.form.submit()">
                <span class="text-danger font-weight-600" style="font-size:.82rem">
                    <i class="fas fa-exclamation-triangle mr-1"></i>Estoque baixo
                </span>
            </label>
            @if(request()->anyFilled(['search','estoque_baixo']))
            <a href="{{ route('pecas.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times"></i>
            </a>
            @endif
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="pl-3">Nome da Peça</th>
                    <th>Qtd.</th>
                    <th>Mín.</th>
                    <th>Custo</th>
                    <th>Venda</th>
                    <th>Situação</th>
                    <th class="text-right pr-3" width="90">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pecas as $peca)
                @php $baixo = $peca->quantidade <= $peca->estoque_minimo @endphp
                <tr class="{{ $baixo ? 'bg-danger-subtle' : '' }}">
                    <td class="pl-3 font-weight-600">{{ $peca->nome }}</td>
                    <td>
                        <span class="font-weight-700 {{ $baixo ? 'text-danger' : 'text-success' }}">
                            {{ $peca->quantidade }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $peca->estoque_minimo }}</td>
                    <td class="text-muted">{{ $peca->preco_custo ? 'R$ ' . number_format($peca->preco_custo, 2, ',', '.') : '—' }}</td>
                    <td>{{ $peca->preco_venda ? 'R$ ' . number_format($peca->preco_venda, 2, ',', '.') : '—' }}</td>
                    <td>
                        @if($baixo)
                        <span class="badge badge-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Estoque Baixo</span>
                        @else
                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Normal</span>
                        @endif
                    </td>
                    <td class="text-right pr-3">
                        <a href="{{ route('pecas.edit', $peca) }}" class="btn btn-xs btn-outline-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('pecas.destroy', $peca) }}" class="d-inline"
                              onsubmit="return confirm('Excluir peça {{ addslashes($peca->nome) }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger" title="Excluir"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="fas fa-boxes fa-2x d-block mb-2 text-light"></i>
                        Nenhuma peça cadastrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pecas->hasPages())
    <div class="card-footer">{{ $pecas->withQueryString()->links() }}</div>
    @endif
</div>
@endsection

@push('css')
<style>
.bg-danger-subtle { background: #fef2f2 !important; }
</style>
@endpush
