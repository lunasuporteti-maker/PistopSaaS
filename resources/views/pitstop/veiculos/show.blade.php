@extends('adminlte::page')
@section('title', 'Veículo')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $veiculo->marca }} {{ $veiculo->modelo }} — {{ $veiculo->placa }}</h1>
        <div>
            <a href="{{ route('veiculos.edit', $veiculo) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
            <a href="{{ route('veiculos.index') }}" class="btn btn-secondary ml-1"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-danger card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-car mr-1"></i> Dados do Veículo</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Cliente</dt>
                    <dd class="col-7"><a href="{{ route('clientes.show', $veiculo->cliente) }}">{{ $veiculo->cliente->nome }}</a></dd>
                    <dt class="col-5">Placa</dt><dd class="col-7">{{ $veiculo->placa ?? '—' }}</dd>
                    <dt class="col-5">Marca</dt><dd class="col-7">{{ $veiculo->marca ?? '—' }}</dd>
                    <dt class="col-5">Modelo</dt><dd class="col-7">{{ $veiculo->modelo ?? '—' }}</dd>
                    <dt class="col-5">Ano</dt><dd class="col-7">{{ $veiculo->ano ?? '—' }}</dd>
                    <dt class="col-5">Cor</dt><dd class="col-7">{{ $veiculo->cor ?? '—' }}</dd>
                    <dt class="col-5">KM Atual</dt><dd class="col-7">{{ $veiculo->km_atual ? number_format($veiculo->km_atual) . ' km' : '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-tachometer-alt mr-1"></i> Histórico KM</h3></div>
            <div class="card-body p-0">
                @forelse($veiculo->historicoKm->sortByDesc('created_at')->take(10) as $h)
                <div class="list-group-item py-2">
                    <strong>{{ number_format($h->km) }} km</strong>
                    <small class="text-muted float-right">{{ $h->created_at->format('d/m/Y') }}</small>
                    @if($h->observacao)<small class="d-block text-muted">{{ $h->observacao }}</small>@endif
                </div>
                @empty
                <p class="text-center text-muted py-3">Sem registros.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-tools mr-1"></i> Ordens de Serviço</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr><th>Nº OS</th><th>Valor</th><th>Data</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($veiculo->ordensServico as $os)
                        <tr>
                            <td><a href="{{ route('ordens.show', $os) }}">{{ $os->numero_os }}</a></td>
                            <td>R$ {{ number_format($os->valor_total, 2, ',', '.') }}</td>
                            <td>{{ $os->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if($os->finalizado_em)
                                    <span class="badge badge-success">Concluído</span>
                                @else
                                    <span class="badge badge-warning">Em andamento</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Nenhuma OS.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
