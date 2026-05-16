@extends('adminlte::page')
@section('title', 'Controle de Caixa')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0"><i class="fas fa-cash-register mr-2 text-danger"></i>Controle de Caixa</h1>
    <span class="badge badge-{{ $caixaHoje ? 'success' : 'danger' }} px-3 py-2" style="font-size:.9rem">
        <i class="fas fa-circle mr-1" style="font-size:.5rem"></i>
        Caixa {{ $caixaHoje ? 'ABERTO' : 'FECHADO' }}
    </span>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="row">
    {{-- Status do caixa hoje --}}
    <div class="col-md-5">
        @if(!$caixaHoje)
        {{-- Abrir caixa --}}
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-lock-open mr-2 text-success"></i>Abrir Caixa do Dia</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('caixa.abrir') }}">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-600">Saldo Inicial (R$) <span class="text-danger">*</span></label>
                        <input type="number" name="saldo_inicial" class="form-control form-control-lg"
                               step="0.01" min="0" placeholder="0,00" required autofocus>
                        <small class="text-muted">Dinheiro em caixa ao iniciar o dia</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-600">Observação</label>
                        <input type="text" name="observacao_abertura" class="form-control"
                               placeholder="Opcional..." maxlength="300">
                    </div>
                    <button type="submit" class="btn btn-success btn-block btn-lg">
                        <i class="fas fa-lock-open mr-2"></i>Abrir Caixa
                    </button>
                </form>
            </div>
        </div>
        @else
        {{-- Caixa aberto: resumo do dia --}}
        <div class="card card-outline card-success shadow-sm mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-chart-line mr-2"></i>Resumo do Dia</h5>
                <small>Aberto às {{ $caixaHoje->aberto_em->format('H:i') }}</small>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="text-muted small">Saldo Inicial</div>
                        <div class="font-weight-bold text-secondary" style="font-size:1.1rem">
                            R$ {{ number_format($caixaHoje->saldo_inicial, 2, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Entradas</div>
                        <div class="font-weight-bold text-success" style="font-size:1.1rem">
                            + R$ {{ number_format($receitaHoje, 2, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Saídas</div>
                        <div class="font-weight-bold text-danger" style="font-size:1.1rem">
                            - R$ {{ number_format($saidaHoje, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
                <hr>
                @php $saldoEsperado = $caixaHoje->saldo_inicial + $receitaHoje - $saidaHoje; @endphp
                <div class="text-center">
                    <div class="text-muted small">Saldo Esperado no Fechamento</div>
                    <div class="font-weight-bold {{ $saldoEsperado >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:1.5rem">
                        R$ {{ number_format($saldoEsperado, 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Fechar caixa --}}
        <div class="card card-outline card-danger shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-lock mr-2 text-danger"></i>Fechar Caixa</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('caixa.fechar', $caixaHoje) }}">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-600">Saldo Real no Fechamento (R$) <span class="text-danger">*</span></label>
                        <input type="number" name="saldo_final" class="form-control form-control-lg"
                               step="0.01" min="0"
                               value="{{ number_format($saldoEsperado, 2, '.', '') }}"
                               placeholder="0,00" required>
                        <small class="text-muted">Conte o dinheiro físico e informe o valor real</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-600">Observação do Fechamento</label>
                        <input type="text" name="observacao_fechamento" class="form-control"
                               placeholder="Opcional..." maxlength="300">
                    </div>
                    <button type="submit" class="btn btn-danger btn-block"
                            onclick="return confirm('Fechar o caixa de hoje?')">
                        <i class="fas fa-lock mr-2"></i>Fechar Caixa
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Histórico de caixas --}}
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Histórico de Caixas</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Data</th>
                            <th>Abertura</th>
                            <th>Fechamento</th>
                            <th>Diferença</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimosCaixas as $cx)
                        @php $diff = $cx->saldo_final !== null ? ($cx->saldo_final - $cx->saldo_inicial) : null; @endphp
                        <tr>
                            <td>{{ $cx->data->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($cx->saldo_inicial, 2, ',', '.') }}</td>
                            <td>{{ $cx->saldo_final !== null ? 'R$ ' . number_format($cx->saldo_final, 2, ',', '.') : '—' }}</td>
                            <td>
                                @if($diff !== null)
                                <span class="text-{{ $diff >= 0 ? 'success' : 'danger' }} font-weight-bold">
                                    {{ $diff >= 0 ? '+' : '' }}R$ {{ number_format($diff, 2, ',', '.') }}
                                </span>
                                @else —
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $cx->status === 'aberto' ? 'success' : 'secondary' }}">
                                    {{ strtoupper($cx->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Nenhum caixa registrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
