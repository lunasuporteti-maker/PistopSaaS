@extends('adminlte::page')
@section('title', 'Lembretes')

@section('content_header')
    <h1><i class="fas fa-bell mr-1"></i> Lembretes Pendentes</h1>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr><th>Cliente</th><th>Veículo</th><th>Serviço</th><th>Data Lembrete</th><th>Status</th><th>Ação</th></tr>
            </thead>
            <tbody>
                @forelse($lembretes as $l)
                <tr class="{{ $l->data_lembrete->isPast() ? 'table-warning' : '' }}">
                    <td>{{ $l->cliente->nome }}</td>
                    <td>{{ $l->veiculo ? $l->veiculo->marca . ' ' . $l->veiculo->modelo : '—' }}</td>
                    <td>{{ $l->servico_nome }}</td>
                    <td>
                        {{ $l->data_lembrete->format('d/m/Y') }}
                        @if($l->data_lembrete->isPast())
                            <span class="badge badge-warning ml-1">Vencido</span>
                        @endif
                    </td>
                    <td><span class="badge badge-secondary">{{ ucfirst($l->status) }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('lembretes.update', $l) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="enviado">
                            <button class="btn btn-sm btn-success" title="Marcar como enviado">
                                <i class="fas fa-check"></i> Enviado
                            </button>
                        </form>
                        <form method="POST" action="{{ route('lembretes.update', $l) }}" class="d-inline ml-1">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="cancelado">
                            <button class="btn btn-sm btn-secondary" title="Cancelar"><i class="fas fa-times"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">
                    <i class="fas fa-check-circle text-success mr-1"></i> Nenhum lembrete pendente.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($lembretes->hasPages())
    <div class="card-footer">{{ $lembretes->links() }}</div>
    @endif
</div>
@endsection
