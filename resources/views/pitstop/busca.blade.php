@extends('layouts.pitstop')
@section('title', 'Busca')

@section('content_header')
<h1 class="m-0"><i class="fas fa-search mr-2"></i>Busca</h1>
@endsection

@section('content')

<form method="GET" action="{{ route('busca') }}" class="mb-4">
    <div class="input-group">
        <input type="search" name="q" value="{{ $q }}"
               placeholder="Nome do cliente, placa, modelo, número da OS…"
               class="form-control form-control-lg" autofocus>
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
</form>

@if(strlen($q) >= 2)

    @php
        $totalResultados = $resultados['clientes']->count()
            + $resultados['veiculos']->count()
            + $resultados['ordens']->count()
            + $resultados['orcamentos']->count();
    @endphp

    @if($totalResultados === 0)
        <div class="text-center text-muted py-5">
            <i class="fas fa-search fa-3x mb-3 d-block" style="opacity:.3"></i>
            Nenhum resultado para <strong>{{ $q }}</strong>
        </div>
    @else

    <p class="text-muted mb-4">
        <strong>{{ $totalResultados }}</strong> resultado(s) para <strong>{{ $q }}</strong>
    </p>

    {{-- Clientes --}}
    @if($resultados['clientes']->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h6 class="m-0 font-weight-bold"><i class="fas fa-user mr-2"></i>Clientes</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <tbody>
                    @foreach($resultados['clientes'] as $c)
                    <tr>
                        <td class="pl-3">
                            <a href="{{ route('clientes.show', $c) }}" class="font-weight-bold">{{ $c->nome }}</a>
                        </td>
                        <td class="text-muted">{{ $c->telefone }}</td>
                        <td class="text-muted">{{ $c->email }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Veículos --}}
    @if($resultados['veiculos']->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h6 class="m-0 font-weight-bold"><i class="fas fa-car mr-2"></i>Veículos</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <tbody>
                    @foreach($resultados['veiculos'] as $v)
                    <tr>
                        <td class="pl-3">
                            <a href="{{ route('veiculos.show', $v) }}" class="font-weight-bold">{{ $v->placa }}</a>
                        </td>
                        <td>{{ $v->modelo }}</td>
                        <td class="text-muted">{{ $v->cliente?->nome }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Ordens de Serviço --}}
    @if($resultados['ordens']->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h6 class="m-0 font-weight-bold"><i class="fas fa-tools mr-2"></i>Ordens de Serviço</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <tbody>
                    @foreach($resultados['ordens'] as $os)
                    <tr>
                        <td class="pl-3">
                            <a href="{{ route('ordens.show', $os) }}" class="font-weight-bold">{{ $os->numero_os }}</a>
                        </td>
                        <td>{{ $os->cliente?->nome }}</td>
                        <td>{{ $os->veiculo?->placa }}</td>
                        <td>
                            <span class="badge badge-{{ match($os->status) {
                                'concluido' => 'success',
                                'em_servico' => 'warning',
                                'cancelado' => 'danger',
                                default => 'secondary'
                            } }}">{{ ucfirst($os->status) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Orçamentos abertos --}}
    @if($resultados['orcamentos']->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h6 class="m-0 font-weight-bold"><i class="fas fa-file-alt mr-2"></i>Orçamentos em aberto</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <tbody>
                    @foreach($resultados['orcamentos'] as $orc)
                    <tr>
                        <td class="pl-3">
                            <a href="{{ route('orcamentos.show', $orc) }}" class="font-weight-bold">#{{ $orc->id }}</a>
                        </td>
                        <td>{{ $orc->cliente?->nome }}</td>
                        <td>{{ $orc->veiculo?->placa }}</td>
                        <td>
                            <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $orc->status)) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @endif

@elseif(strlen($q) > 0)
    <p class="text-muted">Digite ao menos 2 caracteres para buscar.</p>
@endif

@endsection
