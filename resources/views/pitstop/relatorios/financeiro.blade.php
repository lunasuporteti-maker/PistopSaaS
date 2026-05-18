@extends('adminlte::page')
@section('title', 'Relatório Financeiro')

@section('content_header')
<h1 class="m-0"><i class="fas fa-chart-bar mr-2 text-danger"></i>Relatório Financeiro</h1>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline flex-wrap" style="gap:8px">
            <div class="form-group mr-2"><label class="mr-2 font-weight-600">De:</label>
                <input type="date" name="inicio" class="form-control form-control-sm" value="{{ $inicio->toDateString() }}">
            </div>
            <div class="form-group mr-2"><label class="mr-2 font-weight-600">Até:</label>
                <input type="date" name="fim" class="form-control form-control-sm" value="{{ $fim->toDateString() }}">
            </div>
            <button class="btn btn-sm btn-danger"><i class="fas fa-search mr-1"></i>Gerar</button>
            <a href="{{ route('relatorios.financeiro.export', ['inicio' => $inicio->toDateString(), 'fim' => $fim->toDateString()]) }}"
               class="btn btn-sm btn-success ml-2">
                <i class="fas fa-file-excel mr-1"></i>Excel
            </a>
            <a href="{{ route('relatorios.financeiro.pdf', ['inicio' => $inicio->toDateString(), 'fim' => $fim->toDateString()]) }}"
               class="btn btn-sm btn-danger ml-1" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i>PDF
            </a>
        </form>
    </div>
</div>

@php $lucro = $entradas - $saidas; @endphp

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card card-outline card-success shadow-sm text-center py-3">
            <div class="text-success" style="font-size:2rem;font-weight:800">R$ {{ number_format($entradas, 2, ',', '.') }}</div>
            <div class="text-muted small mt-1"><i class="fas fa-arrow-up mr-1"></i>Entradas (OS pagas)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-danger shadow-sm text-center py-3">
            <div class="text-danger" style="font-size:2rem;font-weight:800">R$ {{ number_format($saidas, 2, ',', '.') }}</div>
            <div class="text-muted small mt-1"><i class="fas fa-arrow-down mr-1"></i>Saídas (lançamentos)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-{{ $lucro >= 0 ? 'info' : 'warning' }} shadow-sm text-center py-3">
            <div class="{{ $lucro >= 0 ? 'text-info' : 'text-warning' }}" style="font-size:2rem;font-weight:800">
                {{ $lucro >= 0 ? '+' : '' }}R$ {{ number_format($lucro, 2, ',', '.') }}
            </div>
            <div class="text-muted small mt-1"><i class="fas fa-balance-scale mr-1"></i>{{ $lucro >= 0 ? 'Saldo positivo' : 'Saldo negativo' }}</div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><h5 class="card-title mb-0">Comparativo Visual</h5></div>
    <div class="card-body">
        <canvas id="chartFinanceiro" height="80"></canvas>
    </div>
</div>

<div class="text-muted text-center mt-2 small">
    Período: {{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }}
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
new Chart(document.getElementById('chartFinanceiro'), {
    type: 'bar',
    data: {
        labels: ['Entradas', 'Saídas', 'Resultado'],
        datasets: [{
            label: 'R$',
            data: [{{ $entradas }}, {{ $saidas }}, {{ $lucro }}],
            backgroundColor: ['rgba(39,174,96,.7)', 'rgba(192,57,43,.7)', '{{ $lucro >= 0 ? "rgba(23,162,184,.7)" : "rgba(243,156,18,.7)" }}'],
            borderColor:     ['#27ae60','#c0392b','{{ $lucro >= 0 ? "#17a2b8" : "#f39c12" }}'],
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR', {minimumFractionDigits:2}) }, grid: { color: '#f0f0f0' } }
        }
    }
});
</script>
@endpush
