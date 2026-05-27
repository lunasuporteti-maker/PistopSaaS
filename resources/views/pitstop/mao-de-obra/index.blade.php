@extends('layouts.pitstop')
@section('title', 'Mão de Obra')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Mão de Obra</h1>
        <a href="{{ route('mao-de-obra.create') }}" class="btn btn-danger"><i class="fas fa-plus"></i> Nova</a>
    </div>
@endsection

@section('content')

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Tempo Est. (h)</th>
                    <th>Status</th>
                    <th width="100">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($itens as $item)
                <tr>
                    <td>{{ $item->nome }}</td>
                    <td>R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                    <td>{{ $item->tempo_estimado_horas ? number_format($item->tempo_estimado_horas, 1, ',', '.') . 'h' : '—' }}</td>
                    <td><span class="badge badge-{{ $item->ativo ? 'success' : 'secondary' }}">{{ $item->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                    <td>
                        <a href="{{ route('mao-de-obra.edit', $item) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('mao-de-obra.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Desativar este item?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-ban"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Nenhum item cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($itens->hasPages())
    <div class="card-footer">{{ $itens->links() }}</div>
    @endif
</div>
@endsection
