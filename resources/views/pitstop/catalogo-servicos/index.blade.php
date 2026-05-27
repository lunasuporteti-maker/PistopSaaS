@extends('layouts.pitstop')
@section('title', 'Catálogo de Serviços')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Catálogo de Serviços</h1>
        <a href="{{ route('catalogo-servicos.create') }}" class="btn btn-danger"><i class="fas fa-plus"></i> Novo Serviço</a>
    </div>
@endsection

@section('content')

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nome</th>
                    <th>Preço Sugerido</th>
                    <th>Tempo Est. (h)</th>
                    <th>Lembrete (dias)</th>
                    <th>Status</th>
                    <th width="100">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($servicos as $servico)
                <tr>
                    <td>{{ $servico->nome }}</td>
                    <td>{{ $servico->preco_sugerido ? 'R$ ' . number_format($servico->preco_sugerido, 2, ',', '.') : '—' }}</td>
                    <td>{{ $servico->tempo_estimado_horas ? number_format($servico->tempo_estimado_horas, 1, ',', '.') . 'h' : '—' }}</td>
                    <td>{{ $servico->dias_lembrete ?? '—' }}</td>
                    <td><span class="badge badge-{{ $servico->ativo ? 'success' : 'secondary' }}">{{ $servico->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                    <td>
                        <a href="{{ route('catalogo-servicos.edit', $servico) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('catalogo-servicos.destroy', $servico) }}" class="d-inline" onsubmit="return confirm('Desativar este serviço?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-ban"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum serviço cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($servicos->hasPages())
    <div class="card-footer">{{ $servicos->links() }}</div>
    @endif
</div>
@endsection
