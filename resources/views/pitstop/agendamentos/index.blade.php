@extends('layouts.pitstop')
@section('title', 'Agendamentos')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 font-weight-bold">
            <i class="fas fa-calendar-alt mr-2 text-danger"></i>Agendamentos
        </h1>
        <small class="text-muted">
            @if($dataInicio === $dataFim)
                {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }}
            @else
                {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}
            @endif
        </small>
    </div>
    <a href="{{ route('agendamentos.create') }}" class="btn btn-danger btn-sm px-3">
        <i class="fas fa-plus mr-1"></i> Novo Agendamento
    </a>
</div>
@endsection

@section('content')

@php
$statusConfig = [
    'agendado'   => 'badge-info',
    'confirmado' => 'badge-success',
    'realizado'  => 'badge-secondary',
    'concluido'  => 'badge-secondary',
    'cancelado'  => 'badge-danger',
];
$statusLabel = [
    'agendado'   => 'Agendado',
    'confirmado' => 'Confirmado',
    'realizado'  => 'Realizado',
    'concluido'  => 'Concluído',
    'cancelado'  => 'Cancelado',
];
@endphp

<div class="card shadow-sm">
    <div class="card-header py-2">
        <form method="GET" class="d-flex align-items-center flex-wrap" style="gap:8px">
            <div class="input-group input-group-sm" style="max-width:160px">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-calendar text-muted"></i></span>
                </div>
                <input type="date" name="data_inicio" class="form-control" value="{{ $dataInicio }}">
            </div>
            <span class="text-muted">até</span>
            <div class="input-group input-group-sm" style="max-width:160px">
                <input type="date" name="data_fim" class="form-control" value="{{ $dataFim }}">
            </div>
            <button class="btn btn-sm btn-danger">Filtrar</button>
            <a href="{{ route('agendamentos.index') }}" class="btn btn-sm btn-outline-secondary">Hoje</a>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="pl-3" width="70">Hora</th>
                    <th>Cliente</th>
                    <th>Veículo</th>
                    <th>Serviço</th>
                    <th>Status</th>
                    <th width="100">Criado em</th>
                    <th class="text-right pr-3" width="110">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agendamentos as $ag)
                <tr>
                    <td class="pl-3">
                        <span class="badge badge-danger">{{ $ag->data_hora->format('H:i') }}</span>
                    </td>
                    <td class="font-weight-600">{{ $ag->cliente->nome }}</td>
                    <td>
                        {{ $ag->veiculo ? $ag->veiculo->marca . ' ' . $ag->veiculo->modelo : '—' }}
                        @if($ag->veiculo?->placa)
                        <span class="badge badge-secondary" style="font-size:.7rem">{{ $ag->veiculo->placa }}</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ $ag->servico ?? '—' }}</td>
                    <td>
                        @php $sc = $statusConfig[$ag->status] ?? 'badge-secondary' @endphp
                        <span class="badge {{ $sc }}">
                            {{ $statusLabel[$ag->status] ?? ucfirst($ag->status) }}
                        </span>
                    </td>
                    <td class="text-muted" style="font-size:.8rem">
                        {{ $ag->created_at->format('d/m H:i') }}
                    </td>
                    <td class="text-right pr-3" style="white-space:nowrap">
                        @if(in_array($ag->status, ['agendado', 'confirmado']))
                        <form method="POST" action="{{ route('agendamentos.iniciar-servico', $ag) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-warning" title="Iniciar Serviço — cria orçamento na fila"
                                    onclick="return confirm('Iniciar serviço? Um orçamento será criado na fila do Kanban.')">
                                <i class="fas fa-play"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('agendamentos.concluir', $ag) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button class="btn btn-xs btn-outline-success" title="Marcar como Concluído">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('agendamentos.edit', $ag) }}" class="btn btn-xs btn-outline-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('agendamentos.destroy', $ag) }}" class="d-inline"
                              onsubmit="return confirm('Excluir agendamento?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger" title="Excluir"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="fas fa-calendar-check fa-2x d-block mb-2 text-light"></i>
                        Nenhum agendamento nesta data.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
