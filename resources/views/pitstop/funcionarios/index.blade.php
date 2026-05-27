@extends('layouts.pitstop')
@section('title', 'Funcionários')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Funcionários</h1>
        <a href="{{ route('funcionarios.create') }}" class="btn btn-danger"><i class="fas fa-plus"></i> Novo Funcionário</a>
    </div>
@endsection

@section('content')

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr><th>Nome</th><th>Cargo</th><th>Salário Base</th><th>Telefone</th><th>Status</th><th width="100">Ações</th></tr>
            </thead>
            <tbody>
                @forelse($funcionarios as $f)
                <tr>
                    <td>{{ $f->nome }}</td>
                    <td>{{ $f->cargo ?? '—' }}</td>
                    <td>{{ $f->salario_base ? 'R$ ' . number_format($f->salario_base, 2, ',', '.') : '—' }}</td>
                    <td>{{ $f->telefone ?? '—' }}</td>
                    <td><span class="badge badge-{{ $f->ativo ? 'success' : 'secondary' }}">{{ $f->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                    <td>
                        <a href="{{ route('funcionarios.edit', $f) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('funcionarios.destroy', $f) }}" class="d-inline" onsubmit="return confirm('Excluir?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum funcionário.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($funcionarios->hasPages())
    <div class="card-footer">{{ $funcionarios->links() }}</div>
    @endif
</div>
@endsection
