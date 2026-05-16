@extends('adminlte::page')
@section('title', 'Relatório de Receita por Serviço')

@section('content_header')
<h1 class="m-0"><i class="fas fa-chart-line mr-2 text-danger"></i>Receita por Serviço</h1>
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
            @if($servicos->count())
            <a href="{{ route('relatorios.lucro-servico.export', ['inicio' => $inicio->toDateString(), 'fim' => $fim->toDateString()]) }}"
               class="btn btn-sm btn-success ml-2">
                <i class="fas fa-file-excel mr-1"></i>Excel
            </a>
            @endif
        </form>
    </div>
</div>

<div class="row">
    {{-- Gráfico --}}
    @if($servicos->count())
    <div class="col-md-5">
        <div class="card shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0">Top Serviços (Receita)</h5></div>
            <div class="card-body">
                <canvas id="chartServicos" height="240"></canvas>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabela --}}
    <div class="col-md-{{ $servicos->count() ? '7' : '12' }}">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Detalhamento</h5>
                @if($servicos->count())
                <div>
                    <strong class="text-success">Total: R$ {{ number_format($servicos->sum('total'), 2, ',', '.') }}</strong>
                    &nbsp;·&nbsp;
                    <span class="text-muted">{{ $servicos->sum('quantidade') }} serviços</span>
                </div>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr><th>Serviço</th><th class="text-center">Qtd</th><th class="text-right">Receita Total</th><th class="text-right">Ticket Médio</th></tr>
                    </thead>
                    <tbody>
                        @forelse($servicos as $s)
                        <tr>
                            <td>{{ $s->servico_nome }}</td>
                            <td class="text-center">{{ $s->quantidade }}x</td>
                            <td class="text-right font-weight-bold text-success">R$ {{ number_format($s->total, 2, ',', '.') }}</td>
                            <td class="text-right text-muted">R$ {{ number_format($s->total / $s->quantidade, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Nenhum serviço concluído no período.</td></tr>
                        @endforelse
                    </tbody>
                    @if($servicos->count())
                    <tfoot class="thead-light">
                        <tr>
                            <th>TOTAL</th>
                            <th class="text-center">{{ $servicos->sum('quantidade') }}x</th>
                            <th class="text-right text-success">R$ {{ number_format($servicos->sum('total'), 2, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@if($servicos->count())
@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
var labels = @json($servicos->pluck('servico_nome')->map(fn($n) => strlen($n) > 20 ? substr($n, 0, 20) . '…' : $n));
var data   = @json($servicos->pluck('total')->map(fn($v) => round($v, 2)));
var colors = ['#c0392b','#e74c3c','#e67e22','#f39c12','#27ae60','#2980b9','#8e44ad','#16a085','#2c3e50','#d35400'];

new Chart(document.getElementById('chartServicos'), {
    type: 'doughnut',
    data: {
        labels: labels,
        datasets: [{ data: data, backgroundColor: colors.slice(0, data.length), borderWidth: 2, borderColor: '#fff' }]
    },
    options: {
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } } },
        cutout: '55%',
    }
});
</script>
@endpush
@endif
