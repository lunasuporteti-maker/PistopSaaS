@extends('layouts.pitstop')
@section('title', 'Ordens de Serviço')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 font-weight-bold"><i class="fas fa-tools mr-2 text-danger"></i>Ordens de Serviço</h1>
        <small class="text-muted">{{ $ordens->total() }} OS no sistema</small>
    </div>
</div>
@endsection

@section('content')

<div class="card shadow-sm">
    <div class="card-header py-2">
        <form method="GET" class="d-flex align-items-center" style="gap:8px">
            <div class="input-group input-group-sm" style="max-width:300px">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="text" name="search" class="form-control"
                       placeholder="Nº OS, cliente, veículo..."
                       value="{{ request('search') }}">
            </div>
            <button class="btn btn-sm btn-danger">Buscar</button>
            @if(request('search'))
            <a href="{{ route('ordens.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="pl-3">Nº OS</th>
                    <th>Cliente</th>
                    <th>Veículo</th>
                    <th>Valor</th>
                    <th>Aberta em</th>
                    <th>Status</th>
                    <th class="text-right pr-3" width="80">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ordens as $os)
                <tr>
                    <td class="pl-3">
                        <a href="{{ route('ordens.show', $os) }}" class="font-weight-700 text-danger">
                            {{ $os->numero_os }}
                        </a>
                    </td>
                    <td class="font-weight-600">{{ $os->cliente->nome }}</td>
                    <td>
                        {{ $os->veiculo->marca }} {{ $os->veiculo->modelo }}
                        <span class="badge badge-secondary ml-1" style="font-size:.7rem">{{ $os->veiculo->placa }}</span>
                    </td>
                    <td class="font-weight-600 {{ $os->finalizado_em ? 'text-success' : '' }}">
                        R$ {{ number_format($os->valor_total, 2, ',', '.') }}
                    </td>
                    <td class="text-muted">{{ $os->created_at->format('d/m/Y') }}</td>
                    <td>
                        @if($os->finalizado_em)
                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Concluído</span>
                        @else
                            <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Em andamento</span>
                        @endif
                    </td>
                    <td class="text-right pr-3">
                        <a href="{{ route('ordens.show', $os) }}" class="btn btn-xs btn-outline-primary" title="Ver OS">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="fas fa-tools fa-2x d-block mb-2 text-light"></i>
                        Nenhuma OS encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($ordens->hasPages())
    <div class="card-footer">{{ $ordens->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
