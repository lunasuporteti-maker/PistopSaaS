@extends('layouts.pitstop')
@section('title', 'Parceiros')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Parceiros</h1>
        <a href="{{ route('parceiros.create') }}" class="btn btn-danger"><i class="fas fa-plus"></i> Novo Parceiro</a>
    </div>
@endsection

@section('content')

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr><th>Nome</th><th>Serviço Prestado</th><th>Telefone</th><th>Status</th><th width="100">Ações</th></tr>
            </thead>
            <tbody>
                @forelse($parceiros as $p)
                <tr>
                    <td>{{ $p->nome }}</td>
                    <td>{{ $p->servico_prestado ?? '—' }}</td>
                    <td>{{ $p->telefone ?? '—' }}</td>
                    <td><span class="badge badge-{{ $p->ativo ? 'success' : 'secondary' }}">{{ $p->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                    <td>
                        <a href="{{ route('parceiros.edit', $p) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('parceiros.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Excluir?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Nenhum parceiro.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($parceiros->hasPages())
    <div class="card-footer">{{ $parceiros->links() }}</div>
    @endif
</div>
@endsection
