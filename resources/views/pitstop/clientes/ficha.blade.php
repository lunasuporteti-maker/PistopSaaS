@extends('layouts.pitstop')
@section('title', 'Ficha do Cliente')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $cliente->nome }}</h1>
        <div>
            @if($cliente->telefone)
            @php
                $wa = 'https://wa.me/55' . preg_replace('/\D/', '', $cliente->telefone);
            @endphp
            <a href="{{ $wa }}" target="_blank" class="btn btn-success">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
            @endif
            <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-warning ml-1">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary ml-1">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
@endsection

@section('content')

<div class="row">
    {{-- Dados do Cliente --}}
    <div class="col-md-4">
        <div class="card card-danger card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user mr-1"></i> Dados</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Telefone</dt>
                    <dd class="col-7">
                        @if($cliente->telefone)
                        <a href="https://wa.me/55{{ preg_replace('/\D/','',$cliente->telefone) }}" target="_blank" class="text-success">
                            <i class="fab fa-whatsapp mr-1"></i>{{ $cliente->telefone }}
                        </a>
                        @else —
                        @endif
                    </dd>
                    <dt class="col-5">E-mail</dt><dd class="col-7">{{ $cliente->email ?? '—' }}</dd>
                    <dt class="col-5">CPF</dt><dd class="col-7">{{ $cliente->cpf ?? '—' }}</dd>
                    <dt class="col-5">Endereço</dt>
                    <dd class="col-7">{{ $cliente->logradouro ? $cliente->enderecoCompleto() : ($cliente->endereco ?? '—') }}</dd>
                    <dt class="col-5">Cadastro</dt><dd class="col-7">{{ $cliente->created_at->format('d/m/Y') }}</dd>
                </dl>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="text-danger font-weight-bold" style="font-size:1.4rem">{{ $cliente->ordensServico->count() }}</div>
                        <small class="text-muted">OS Realizadas</small>
                    </div>
                    <div class="col-6">
                        <div class="text-success font-weight-bold" style="font-size:1.1rem">
                            R$ {{ number_format($cliente->ordensServico->sum('valor_total'), 2, ',', '.') }}
                        </div>
                        <small class="text-muted">Total Gasto</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Veículos --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-car mr-1"></i> Veículos ({{ $cliente->veiculos->count() }})</h3>
                <a href="{{ route('veiculos.create') }}?cliente_id={{ $cliente->id }}" class="btn btn-sm btn-danger">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($cliente->veiculos as $v)
                <div class="list-group-item">
                    <strong>{{ $v->marca }} {{ $v->modelo }}</strong>
                    <small class="text-muted d-block">{{ $v->placa }} — {{ $v->ano }} — {{ $v->cor }}</small>
                    <small class="text-muted">KM: {{ number_format($v->km_atual ?? 0) }}</small>
                </div>
                @empty
                <p class="text-center text-muted py-3">Nenhum veículo.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Histórico de OS --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-tools mr-1"></i> Histórico de Ordens de Serviço</h3>
                <a href="{{ route('orcamentos.create') }}?cliente_id={{ $cliente->id }}" class="btn btn-sm btn-danger">
                    <i class="fas fa-plus"></i> Novo Orçamento
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr><th>Nº OS</th><th>Veículo</th><th>Valor</th><th>Data</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($cliente->ordensServico as $os)
                        <tr>
                            <td><a href="{{ route('ordens.show', $os) }}">{{ $os->numero_os }}</a></td>
                            <td>{{ $os->veiculo->marca }} {{ $os->veiculo->modelo }}</td>
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
                        <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma OS.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Orçamentos --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-file-invoice mr-1"></i> Orçamentos</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr><th>Veículo</th><th>Valor</th><th>Status</th><th>Data</th></tr>
                    </thead>
                    <tbody>
                        @forelse($cliente->orcamentos as $orc)
                        <tr>
                            <td>{{ $orc->veiculo->marca }} {{ $orc->veiculo->modelo }}</td>
                            <td>R$ {{ number_format($orc->valor_total, 2, ',', '.') }}</td>
                            <td><span class="badge badge-secondary">{{ $orc->status }}</span></td>
                            <td>{{ $orc->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Nenhum orçamento.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
