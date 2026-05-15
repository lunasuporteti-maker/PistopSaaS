@extends('adminlte::page')
@section('title', 'Dashboard')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 font-weight-bold" style="font-size:1.35rem">
            <i class="fas fa-tachometer-alt mr-2 text-danger"></i>Dashboard
        </h1>
        <small class="text-muted">{{ \Carbon\Carbon::now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</small>
    </div>
    <a href="{{ route('kanban') }}" target="_blank" class="btn btn-danger btn-sm px-3">
        <i class="fas fa-columns mr-1"></i> Kanban
    </a>
</div>
@endsection

@section('content')

{{-- ── KPI Cards ──────────────────────────────────────────── --}}
<div class="row" style="gap:0">
    <div class="col-6 col-lg-3 mb-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="fas fa-coins"></i></div>
            <div class="kpi-body">
                <div class="kpi-value">R$ {{ number_format($receitaHoje, 2, ',', '.') }}</div>
                <div class="kpi-label">Receita Hoje</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
            <div class="kpi-body">
                <div class="kpi-value">R$ {{ number_format($receitaMes, 2, ',', '.') }}</div>
                <div class="kpi-label">Receita do Mês</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="kpi-card kpi-red">
            <div class="kpi-icon"><i class="fas fa-arrow-trend-down"></i></div>
            <div class="kpi-body">
                <div class="kpi-value">R$ {{ number_format($saidasMes, 2, ',', '.') }}</div>
                <div class="kpi-label">Saídas do Mês</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        @php $lucro = $receitaMes - $saidasMes; @endphp
        <div class="kpi-card {{ $lucro >= 0 ? 'kpi-emerald' : 'kpi-orange' }}">
            <div class="kpi-icon"><i class="fas fa-scale-balanced"></i></div>
            <div class="kpi-body">
                <div class="kpi-value">R$ {{ number_format($lucro, 2, ',', '.') }}</div>
                <div class="kpi-label">Lucro do Mês</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Alertas inline ─────────────────────────────────────── --}}
@if($estoqueBaixo->count() || $fila->count())
<div class="d-flex flex-wrap gap-2 mb-3">
    @if($fila->count())
    <a href="{{ route('fila') }}" class="dash-pill dash-pill-blue">
        <i class="fas fa-list-ol mr-1"></i>
        <strong>{{ $fila->count() }}</strong> na fila
    </a>
    <a href="{{ route('kanban') }}" target="_blank" class="dash-pill dash-pill-outline">
        <i class="fas fa-columns mr-1"></i> Kanban
    </a>
    @endif
    @if($estoqueBaixo->count())
    <a href="{{ route('pecas.index', ['estoque_baixo'=>1]) }}" class="dash-pill dash-pill-orange">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        <strong>{{ $estoqueBaixo->count() }}</strong> peça(s) em estoque baixo
    </a>
    @endif
</div>
@endif

{{-- ── Gráficos linha 1 ───────────────────────────────────── --}}
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <span class="font-weight-600 text-dark" style="font-size:.9rem">
                    <i class="fas fa-chart-bar mr-2 text-danger"></i>Receita × Saída — últimos 6 meses
                </span>
            </div>
            <div class="card-body py-2 px-3">
                <canvas id="chartReceita" style="max-height:220px"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2">
                <span class="font-weight-600 text-dark" style="font-size:.9rem">
                    <i class="fas fa-chart-pie mr-2 text-danger"></i>Orçamentos do Mês
                </span>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-2">
                <canvas id="chartStatus" style="max-height:190px;max-width:190px"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── Linha 2: top serviços + fila ──────────────────────── --}}
<div class="row">
    <div class="col-lg-5 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2">
                <span class="font-weight-600 text-dark" style="font-size:.9rem">
                    <i class="fas fa-wrench mr-2 text-danger"></i>Top Serviços do Mês
                </span>
            </div>
            <div class="card-body py-2 px-3">
                <canvas id="chartServicos" style="max-height:200px"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-7 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <span class="font-weight-600 text-dark" style="font-size:.9rem">
                    <i class="fas fa-list-ol mr-2 text-danger"></i>Fila de Serviço
                </span>
                <a href="{{ route('kanban') }}" target="_blank" class="btn btn-xs btn-outline-danger">
                    <i class="fas fa-columns mr-1"></i>Kanban
                </a>
            </div>
            <div class="card-body p-0">
                @if($fila->isEmpty())
                <div class="text-center text-muted py-4" style="font-size:.85rem">
                    <i class="fas fa-check-circle text-success d-block mb-2" style="font-size:1.4rem"></i>
                    Nenhum veículo em fila.
                </div>
                @else
                <table class="table table-sm table-hover mb-0" style="font-size:.85rem">
                    <thead><tr>
                        <th class="pl-3">#</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Status</th>
                    </tr></thead>
                    <tbody>
                        @foreach($fila as $item)
                        <tr>
                            <td class="pl-3 text-muted">{{ $item->posicao_fila ?? '—' }}</td>
                            <td class="font-weight-600">{{ $item->cliente->nome }}</td>
                            <td>{{ $item->veiculo->marca }} {{ $item->veiculo->modelo }}
                                <small class="text-muted">· {{ $item->veiculo->placa }}</small>
                            </td>
                            <td>
                                @if($item->status === 'em_servico')
                                    <span class="badge badge-warning">Em Serviço</span>
                                @else
                                    <span class="badge badge-info">Aprovado</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Agendamentos hoje + Últimas OS ────────────────────── --}}
<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-header py-2">
                <span class="font-weight-600 text-dark" style="font-size:.9rem">
                    <i class="fas fa-calendar-day mr-2 text-danger"></i>Agendamentos Hoje
                </span>
            </div>
            <div class="card-body p-0">
                @if($agendamentosHoje->isEmpty())
                <div class="text-center text-muted py-4" style="font-size:.85rem">
                    <i class="fas fa-calendar-check text-success d-block mb-2" style="font-size:1.4rem"></i>
                    Nenhum agendamento hoje.
                </div>
                @else
                <ul class="list-group list-group-flush">
                    @foreach($agendamentosHoje as $ag)
                    <li class="list-group-item py-2 px-3" style="font-size:.85rem">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge badge-danger mr-1">{{ $ag->data_hora->format('H:i') }}</span>
                                <strong>{{ $ag->cliente->nome }}</strong>
                                <div class="text-muted small mt-1">{{ $ag->servico }}</div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-3">
        <div class="card shadow-sm">
            <div class="card-header py-2">
                <span class="font-weight-600 text-dark" style="font-size:.9rem">
                    <i class="fas fa-tools mr-2 text-danger"></i>Últimas OS Concluídas
                </span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" style="font-size:.85rem">
                    <thead><tr>
                        <th class="pl-3">Nº OS</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Valor</th>
                        <th>Data</th>
                    </tr></thead>
                    <tbody>
                        @forelse($ultimasOs as $os)
                        <tr>
                            <td class="pl-3">
                                <a href="{{ route('ordens.show', $os) }}" class="text-danger font-weight-600">
                                    {{ $os->numero_os }}
                                </a>
                            </td>
                            <td>{{ $os->cliente->nome }}</td>
                            <td>{{ $os->veiculo->marca }} {{ $os->veiculo->modelo }}</td>
                            <td class="font-weight-600 text-success">R$ {{ number_format($os->valor_total, 2, ',', '.') }}</td>
                            <td class="text-muted">{{ $os->finalizado_em->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma OS concluída.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
/* ── KPI Cards ─────────────────────────────────── */
.kpi-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
    height: 80px;
    transition: transform .15s, box-shadow .15s;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }

.kpi-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.kpi-value { font-size: 1.15rem; font-weight: 800; line-height: 1.2; color: inherit; }
.kpi-label { font-size: 0.75rem; font-weight: 500; opacity: .8; margin-top: 2px; }

/* Green */
.kpi-green { background: linear-gradient(135deg, #1a9c47, #27ae60); color: #fff; }
.kpi-green .kpi-icon { background: rgba(255,255,255,.2); color: #fff; }
/* Blue */
.kpi-blue { background: linear-gradient(135deg, #1565c0, #1976d2); color: #fff; }
.kpi-blue .kpi-icon { background: rgba(255,255,255,.2); color: #fff; }
/* Red */
.kpi-red { background: linear-gradient(135deg, #b71c1c, #c0392b); color: #fff; }
.kpi-red .kpi-icon { background: rgba(255,255,255,.2); color: #fff; }
/* Emerald */
.kpi-emerald { background: linear-gradient(135deg, #00695c, #00897b); color: #fff; }
.kpi-emerald .kpi-icon { background: rgba(255,255,255,.2); color: #fff; }
/* Orange */
.kpi-orange { background: linear-gradient(135deg, #e65100, #f57c00); color: #fff; }
.kpi-orange .kpi-icon { background: rgba(255,255,255,.2); color: #fff; }

/* ── Dash Pills ─────────────────────────────────── */
.dash-pill {
    display: inline-flex; align-items: center;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none !important;
    transition: opacity .15s;
}
.dash-pill:hover { opacity: .85; }
.dash-pill-blue { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
.dash-pill-orange { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
.dash-pill-outline { background: #fff; color: #555; border: 1px solid #ddd; }

.btn-xs { padding: 3px 8px; font-size: .75rem; }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#6c757d';
Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
Chart.defaults.font.size = 11;

// ── 1. Receita x Saída ─────────────────────────────────────
new Chart(document.getElementById('chartReceita'), {
    type: 'bar',
    data: {
        labels: @json($chartReceitaLabels),
        datasets: [
            {
                label: 'Receita (R$)',
                data:  @json($chartReceitaEntrada),
                backgroundColor: 'rgba(26,156,71,0.8)',
                borderColor:     '#1a9c47',
                borderWidth: 0,
                borderRadius: 6,
                borderSkipped: false,
            },
            {
                label: 'Saídas (R$)',
                data:  @json($chartReceitaSaida),
                backgroundColor: 'rgba(192,57,43,0.75)',
                borderColor:     '#c0392b',
                borderWidth: 0,
                borderRadius: 6,
                borderSkipped: false,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'top', labels: { boxWidth: 10, padding: 12 } },
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,.05)' },
                ticks: { callback: v => 'R$' + v.toLocaleString('pt-BR') }
            },
            x: { grid: { display: false } },
        },
    },
});

// ── 2. Status orçamentos ────────────────────────────────────
new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: {
        labels: @json($chartStatusLabels),
        datasets: [{
            data:  @json($chartStatusData),
            backgroundColor: ['#78909c','#1976d2','#f57c00','#1a9c47','#c0392b'],
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 6,
        }],
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { padding: 10, boxWidth: 10, font: { size: 10 } } },
        },
    },
});

// ── 3. Top serviços ─────────────────────────────────────────
new Chart(document.getElementById('chartServicos'), {
    type: 'bar',
    data: {
        labels: @json($chartServicosLabels),
        datasets: [{
            label: 'Qtd',
            data:  @json($chartServicosData),
            backgroundColor: [
                'rgba(192,57,43,.8)','rgba(26,118,210,.8)',
                'rgba(26,156,71,.8)','rgba(245,124,0,.8)',
                'rgba(120,144,156,.8)',
            ],
            borderRadius: 5,
            borderWidth: 0,
        }],
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,.05)' } },
            y: { grid: { display: false } },
        },
    },
});
</script>
@endpush
