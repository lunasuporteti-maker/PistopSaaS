@extends('layouts.pitstop')
@section('title', 'Agenda da Semana')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="m-0 font-weight-bold">
            <i class="fas fa-calendar-week mr-2 text-danger"></i>Agenda da Semana
        </h1>
        <small class="text-muted">
            {{ $inicio->format('d/m') }} a {{ $fim->format('d/m/Y') }}
        </small>
    </div>
    <div class="d-flex align-items-center" style="gap:8px">
        <a href="{{ route('agendamentos.semana', ['semana' => $inicio->copy()->subWeek()->toDateString()]) }}"
           class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-left"></i></a>
        <a href="{{ route('agendamentos.semana') }}" class="btn btn-sm btn-outline-secondary">Hoje</a>
        <a href="{{ route('agendamentos.semana', ['semana' => $inicio->copy()->addWeek()->toDateString()]) }}"
           class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-right"></i></a>
        <a href="{{ route('agendamentos.index') }}" class="btn btn-sm btn-secondary ml-2">
            <i class="fas fa-list mr-1"></i> Lista
        </a>
        <a href="{{ route('agendamentos.create') }}" class="btn btn-sm btn-danger">
            <i class="fas fa-plus mr-1"></i> Novo
        </a>
    </div>
</div>
@endsection

@push('css')
<style>
.cal-table { table-layout: fixed; width: 100%; border-collapse: collapse; }
.cal-table th, .cal-table td { border: 1px solid #e2e8f0; vertical-align: top; }
.cal-table th { background: #f8fafc; font-size: .78rem; text-align: center; padding: 6px 4px; }
.cal-table .hora-cell { width: 52px; font-size: .72rem; color: #94a3b8; text-align: right; padding: 4px 6px 0 0; white-space: nowrap; }
.cal-table td { height: 52px; padding: 2px; }
.cal-table td.hoje { background: #fef9f0; }
.ag-chip { display: block; border-radius: 4px; padding: 2px 6px; font-size: .72rem; margin-bottom: 2px;
           white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; cursor: pointer; }
.ag-chip.agendado   { background: #dbeafe; color: #1e40af; }
.ag-chip.confirmado { background: #dcfce7; color: #166534; }
.ag-chip.realizado  { background: #f1f5f9; color: #64748b; }
.ag-chip.cancelado  { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@section('content')

<div class="card shadow-sm" style="overflow-x:auto">
    <div class="card-body p-0">
        <table class="cal-table">
            <thead>
                <tr>
                    <th style="width:52px"></th>
                    @foreach($dias as $dia)
                    <th class="{{ $dia->isToday() ? 'text-danger' : '' }}">
                        <div>{{ $dia->isoFormat('ddd') }}</div>
                        <div style="font-size:1rem;font-weight:{{ $dia->isToday() ? '700' : '400' }}">
                            {{ $dia->format('d') }}
                        </div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($horas as $hora)
                <tr>
                    <td class="hora-cell">{{ str_pad($hora, 2, '0', STR_PAD_LEFT) }}h</td>
                    @foreach($dias as $dia)
                    @php
                        $chave  = $dia->format('Y-m-d');
                        $slotAg = ($agendamentos[$chave] ?? collect())->filter(
                            fn($a) => (int) $a->data_hora->format('H') === $hora
                        );
                    @endphp
                    <td class="{{ $dia->isToday() ? 'hoje' : '' }}">
                        @foreach($slotAg as $ag)
                        <a href="{{ route('agendamentos.edit', $ag) }}"
                           class="ag-chip {{ $ag->status }}"
                           title="{{ $ag->cliente->nome }} — {{ $ag->servico ?? 'Sem serviço' }}">
                            {{ $ag->data_hora->format('H:i') }} {{ $ag->cliente->nome }}
                        </a>
                        @endforeach
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex mt-3" style="gap:12px;font-size:.78rem">
    <span><span class="ag-chip agendado d-inline-block" style="width:60px;text-align:center">Agendado</span></span>
    <span><span class="ag-chip confirmado d-inline-block" style="width:60px;text-align:center">Confirmado</span></span>
    <span><span class="ag-chip realizado d-inline-block" style="width:60px;text-align:center">Realizado</span></span>
    <span><span class="ag-chip cancelado d-inline-block" style="width:60px;text-align:center">Cancelado</span></span>
</div>

@endpush

@endsection
