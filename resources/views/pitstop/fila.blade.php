@extends('layouts.pitstop')
@section('title', 'Fila de Serviço')

@section('content_header')
    <h1><i class="fas fa-list-ol mr-1"></i> Fila de Serviço</h1>
@endsection

@section('content')

@if($fila->isEmpty())
<div class="alert alert-info"><i class="fas fa-info-circle mr-1"></i> Nenhum veículo em fila no momento.</div>
@else
<div class="row">
    @foreach($fila as $orc)
    <div class="col-md-4 col-sm-6">
        <div class="card card-{{ $orc->status === 'em_servico' ? 'warning' : 'info' }} card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="badge badge-{{ $orc->status === 'em_servico' ? 'warning' : 'info' }} mr-1">
                        {{ $orc->posicao_fila ?? '#' }}
                    </span>
                    {{ $orc->status === 'em_servico' ? 'Em Serviço' : 'Aguardando' }}
                </h3>
            </div>
            <div class="card-body">
                <h5 class="mb-1">{{ $orc->cliente->nome }}</h5>
                <p class="mb-1 text-muted">
                    <i class="fas fa-car mr-1"></i>
                    {{ $orc->veiculo->marca }} {{ $orc->veiculo->modelo }}
                    — {{ $orc->veiculo->placa ?? 'S/placa' }}
                </p>
                @if($orc->queixa_cliente)
                <p class="mb-0"><small><i class="fas fa-comment mr-1"></i> {{ Str::limit($orc->queixa_cliente, 80) }}</small></p>
                @endif
                <p class="mb-0 mt-1 text-muted"><small>Entrada: {{ $orc->aprovado_em?->format('d/m H:i') ?? $orc->created_at->format('d/m H:i') }}</small></p>
            </div>
            <div class="card-footer p-2">
                <a href="{{ route('orcamentos.show', $orc) }}" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-eye"></i> Ver Orçamento
                </a>
                @if($orc->ordemServico)
                <a href="{{ route('ordens.show', $orc->ordemServico) }}" class="btn btn-sm btn-outline-success ml-1">
                    <i class="fas fa-tools"></i> Ver OS
                </a>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
