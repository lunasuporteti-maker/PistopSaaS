@extends('layouts.pitstop')
@section('title', 'Buscar por Placa')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 font-weight-bold"><i class="fas fa-search mr-2 text-danger"></i>Buscar por Placa</h1>
        <small class="text-muted">Digite a placa do veículo para encontrar o cliente e o histórico.</small>
    </div>
    <a href="{{ route('veiculos.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Veículos
    </a>
</div>
@endsection

@section('content')

{{-- ── Campo de busca em destaque ───────────────────────────────── --}}
<div class="card card-outline card-danger shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('veiculos.buscar-placa') }}" class="d-flex flex-wrap align-items-center" style="gap:8px">
            <div class="input-group input-group-lg" style="max-width:360px">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-car text-muted"></i></span>
                </div>
                <input type="text" name="placa" class="form-control text-uppercase"
                       style="letter-spacing:2px;font-weight:600"
                       placeholder="ABC1234 ou ABC1D23"
                       value="{{ $placaInput }}" autofocus autocomplete="off">
            </div>
            <button class="btn btn-lg btn-danger px-4"><i class="fas fa-search mr-1"></i> Buscar</button>
        </form>
    </div>
</div>

{{-- ── Orientação (placa vazia/curta) ───────────────────────────── --}}
@if($erro)
<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i> {{ $erro }}
</div>

{{-- ── Resultado da busca ───────────────────────────────────────── --}}
@elseif(!is_null($veiculos))

    @if($veiculos->isEmpty())
    {{-- RAMO: não encontrada --}}
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-car-crash fa-3x text-muted mb-3 d-block"></i>
            <h4 class="mb-2">Nenhum veículo encontrado com a placa <span class="badge badge-dark" style="letter-spacing:1px">{{ $placaNormalizada }}</span> nesta oficina.</h4>
            <p class="text-muted">Você pode cadastrar o veículo (a placa já vem preenchida) ou cadastrar um novo cliente.</p>
            <div class="mt-3">
                <a href="{{ route('veiculos.create', ['placa' => $placaNormalizada]) }}" class="btn btn-danger">
                    <i class="fas fa-plus mr-1"></i> Cadastrar novo veículo
                </a>
                <a href="{{ route('clientes.create') }}" class="btn btn-outline-secondary ml-2">
                    <i class="fas fa-user-plus mr-1"></i> Cadastrar novo cliente
                </a>
            </div>
        </div>
    </div>

    @else
    {{-- RAMO: encontrada (1 resultado = direto; vários = lista de cards) --}}
    @if($veiculos->count() > 1)
    <p class="text-muted mb-2"><strong>{{ $veiculos->count() }}</strong> veículos encontrados para <strong>{{ $placaNormalizada }}</strong>.</p>
    @endif

    @foreach($veiculos as $veiculo)
    @php
        $tipoLabel = match($veiculo->tipo_veiculo ?? 'carro') {
            'moto'     => ['🏍️ Moto',     'fas fa-motorcycle', 'text-warning'],
            'caminhao' => ['🚚 Caminhão',  'fas fa-truck',      'text-secondary'],
            'van'      => ['🚐 Van/Kombi', 'fas fa-shuttle-van','text-info'],
            'outro'    => ['🚜 Outro',     'fas fa-cog',        'text-muted'],
            default    => ['🚗 Carro',     'fas fa-car',        'text-danger'],
        };
    @endphp
    <div class="card card-outline card-success shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <span class="badge badge-dark mr-2" style="font-size:.9rem;letter-spacing:1px">{{ $veiculo->placa ?? '—' }}</span>
                {{ $veiculo->marca }} {{ $veiculo->modelo }}
            </h3>
            <a href="{{ route('veiculos.show', $veiculo) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-history mr-1"></i> Ver histórico
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small mb-2"><i class="{{ $tipoLabel[1] }} {{ $tipoLabel[2] }} mr-1"></i> Veículo</h6>
                    <dl class="row mb-0">
                        <dt class="col-4">Tipo</dt><dd class="col-8">{{ $tipoLabel[0] }}</dd>
                        <dt class="col-4">Marca/Modelo</dt><dd class="col-8">{{ $veiculo->marca ?? '—' }} {{ $veiculo->modelo ?? '' }}</dd>
                        <dt class="col-4">Ano</dt><dd class="col-8">{{ $veiculo->ano ?? '—' }}</dd>
                        <dt class="col-4">Cor</dt><dd class="col-8">{{ $veiculo->cor ?? '—' }}</dd>
                        <dt class="col-4">KM</dt><dd class="col-8">{{ $veiculo->km_atual ? number_format($veiculo->km_atual) . ' km' : '—' }}</dd>
                    </dl>
                </div>
                <div class="col-md-6 border-left">
                    <h6 class="text-muted text-uppercase small mb-2"><i class="fas fa-user mr-1"></i> Cliente dono</h6>
                    @if($veiculo->cliente)
                    <dl class="row mb-2">
                        <dt class="col-4">Nome</dt><dd class="col-8">{{ $veiculo->cliente->nome }}</dd>
                        @if($veiculo->cliente->telefone)
                        <dt class="col-4">Telefone</dt><dd class="col-8">{{ $veiculo->cliente->telefone }}</dd>
                        @endif
                    </dl>
                    <a href="{{ route('clientes.show', $veiculo->cliente) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-id-card mr-1"></i> Perfil do cliente
                    </a>
                    <a href="{{ route('clientes.ficha', $veiculo->cliente) }}" class="btn btn-sm btn-outline-secondary ml-1">
                        <i class="fas fa-file-alt mr-1"></i> Ficha do cliente
                    </a>
                    @else
                    <p class="text-muted">Sem cliente vinculado.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif

@endif
@endsection
