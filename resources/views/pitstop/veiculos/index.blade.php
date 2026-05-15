@extends('adminlte::page')
@section('title', 'Veículos')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 font-weight-bold"><i class="fas fa-car mr-2 text-danger"></i>Veículos</h1>
        <small class="text-muted">{{ $veiculos->total() }} veículo(s) cadastrado(s)</small>
    </div>
    <a href="{{ route('veiculos.create') }}" class="btn btn-danger btn-sm px-3">
        <i class="fas fa-plus mr-1"></i> Novo Veículo
    </a>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="card shadow-sm">
    <div class="card-header py-2">
        <form method="GET" class="d-flex align-items-center" style="gap:8px">
            <div class="input-group input-group-sm" style="max-width:300px">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="text" name="search" class="form-control"
                       placeholder="Placa, modelo ou cliente..."
                       value="{{ request('search') }}">
            </div>
            <button class="btn btn-sm btn-danger">Buscar</button>
            @if(request('search'))
            <a href="{{ route('veiculos.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="pl-3">Placa</th>
                    <th>Veículo</th>
                    <th>Ano</th>
                    <th>Cor</th>
                    <th>KM</th>
                    <th>Proprietário</th>
                    <th class="text-right pr-3" width="110">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($veiculos as $v)
                <tr>
                    <td class="pl-3">
                        <span class="badge badge-dark" style="font-size:.8rem;letter-spacing:1px">
                            {{ $v->placa ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <span class="font-weight-600">{{ $v->marca }}</span>
                        {{ $v->modelo }}
                    </td>
                    <td class="text-muted">{{ $v->ano ?? '—' }}</td>
                    <td class="text-muted">{{ $v->cor ?? '—' }}</td>
                    <td>{{ $v->km_atual ? number_format($v->km_atual) . ' km' : '—' }}</td>
                    <td>
                        <a href="{{ route('clientes.show', $v->cliente) }}" class="text-dark font-weight-600">
                            {{ $v->cliente->nome }}
                        </a>
                    </td>
                    <td class="text-right pr-3">
                        <a href="{{ route('veiculos.show', $v) }}" class="btn btn-xs btn-outline-primary" title="Ver"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('veiculos.edit', $v) }}" class="btn btn-xs btn-outline-secondary" title="Editar"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('veiculos.destroy', $v) }}" class="d-inline"
                              onsubmit="return confirm('Excluir veículo {{ addslashes($v->placa) }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger" title="Excluir"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="fas fa-car fa-2x d-block mb-2 text-light"></i>
                        Nenhum veículo encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($veiculos->hasPages())
    <div class="card-footer">{{ $veiculos->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
