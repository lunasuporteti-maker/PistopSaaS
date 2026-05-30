@extends('layouts.pitstop')
@section('title', 'Margem por OS')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="m-0"><i class="fas fa-percentage mr-2 text-warning"></i>Margem por OS</h1>
    <a href="{{ route('relatorios.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Voltar
    </a>
</div>
@endsection

@section('content')

<form method="GET" class="card shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap gap-3 align-items-end" style="gap:12px">
        <div>
            <label class="form-label small mb-1">De</label>
            <input type="date" name="inicio" value="{{ $inicio->format('Y-m-d') }}" class="form-control form-control-sm">
        </div>
        <div>
            <label class="form-label small mb-1">Até</label>
            <input type="date" name="fim" value="{{ $fim->format('Y-m-d') }}" class="form-control form-control-sm">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-search mr-1"></i> Filtrar
        </button>
    </div>
</form>

{{-- Totais --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Receita total</div>
            <div class="font-weight-bold" style="font-size:1.2rem;color:#27ae60">
                R$ {{ number_format($totais['receita'], 2, ',', '.') }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Custo de peças</div>
            <div class="font-weight-bold" style="font-size:1.2rem;color:#e74c3c">
                R$ {{ number_format($totais['custo_pecas'], 2, ',', '.') }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Margem bruta</div>
            <div class="font-weight-bold" style="font-size:1.2rem;color:{{ $totais['margem'] >= 0 ? '#2980b9' : '#e74c3c' }}">
                R$ {{ number_format($totais['margem'], 2, ',', '.') }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card shadow-sm text-center py-3">
            <div class="text-muted small mb-1">% Margem</div>
            <div class="font-weight-bold" style="font-size:1.2rem;color:{{ ($totais['margem_pct'] ?? 0) >= 30 ? '#27ae60' : (($totais['margem_pct'] ?? 0) >= 0 ? '#f39c12' : '#e74c3c') }}">
                {{ $totais['margem_pct'] !== null ? $totais['margem_pct'] . '%' : '—' }}
            </div>
        </div>
    </div>
</div>

{{-- Tabela --}}
<div class="card shadow-sm">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">
            {{ $ordens->count() }} OS concluídas
            — {{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }}
        </h6>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th class="pl-3">OS</th>
                    <th>Cliente</th>
                    <th>Placa</th>
                    <th>Finalizada</th>
                    <th class="text-right">Receita</th>
                    <th class="text-right">Custo Peças</th>
                    <th class="text-right">Margem</th>
                    <th class="text-right pr-3">%</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ordens as $row)
                @php
                    $corPct = ($row['margem_pct'] ?? 0) >= 30 ? 'success'
                        : (($row['margem_pct'] ?? 0) >= 0 ? 'warning' : 'danger');
                @endphp
                <tr>
                    <td class="pl-3">
                        <a href="{{ route('ordens.show', $row['os']) }}">
                            {{ $row['os']->numero_os }}
                        </a>
                    </td>
                    <td>{{ $row['os']->cliente?->nome ?? '—' }}</td>
                    <td>{{ $row['os']->veiculo?->placa ?? '—' }}</td>
                    <td style="white-space:nowrap">{{ $row['os']->finalizado_em->format('d/m/Y') }}</td>
                    <td class="text-right">R$ {{ number_format($row['receita'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($row['custo_pecas'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($row['margem'], 2, ',', '.') }}</td>
                    <td class="text-right pr-3">
                        @if($row['margem_pct'] !== null)
                            <span class="badge badge-{{ $corPct }}">{{ $row['margem_pct'] }}%</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        Nenhuma OS concluída no período.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
