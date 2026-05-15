@extends('adminlte::page')
@section('title', 'Clientes')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 font-weight-bold"><i class="fas fa-users mr-2 text-danger"></i>Clientes</h1>
        <small class="text-muted">{{ $clientes->total() }} cliente(s) cadastrado(s)</small>
    </div>
    <a href="{{ route('clientes.create') }}" class="btn btn-danger btn-sm px-3">
        <i class="fas fa-plus mr-1"></i> Novo Cliente
    </a>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="card shadow-sm">
    <div class="card-header py-2">
        <form method="GET" class="d-flex align-items-center gap-2" style="gap:8px">
            <div class="input-group input-group-sm" style="max-width:320px">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="text" name="search" class="form-control"
                       placeholder="Nome, telefone ou CPF..."
                       value="{{ request('search') }}">
            </div>
            <button class="btn btn-sm btn-danger">Buscar</button>
            @if(request('search'))
            <a href="{{ route('clientes.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times"></i>
            </a>
            @endif
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="pl-3">Nome</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>CPF</th>
                    <th class="text-right pr-3" width="120">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr>
                    <td class="pl-3">
                        <a href="{{ route('clientes.show', $cliente) }}" class="font-weight-600 text-dark">
                            {{ $cliente->nome }}
                        </a>
                    </td>
                    <td>
                        @if($cliente->telefone)
                        <a href="https://wa.me/55{{ preg_replace('/\D/','',$cliente->telefone) }}" target="_blank" class="text-success">
                            <i class="fab fa-whatsapp mr-1"></i>{{ $cliente->telefone }}
                        </a>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ $cliente->email ?? '—' }}</td>
                    <td class="text-muted">{{ $cliente->cpf ?? '—' }}</td>
                    <td class="text-right pr-3">
                        <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-xs btn-outline-primary" title="Ficha">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-xs btn-outline-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" class="d-inline"
                              onsubmit="return confirm('Excluir {{ addslashes($cliente->nome) }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger" title="Excluir"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="fas fa-users fa-2x d-block mb-2 text-light"></i>
                        Nenhum cliente encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clientes->hasPages())
    <div class="card-footer">{{ $clientes->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
