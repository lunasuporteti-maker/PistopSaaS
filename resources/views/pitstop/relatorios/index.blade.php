@extends('layouts.pitstop')
@section('title', 'Relatórios')

@section('content_header')
<h1 class="m-0"><i class="fas fa-chart-bar mr-2 text-danger"></i>Relatórios</h1>
@endsection

@section('content')

<div class="row">

    {{-- Relatório Financeiro --}}
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100" style="border-top:3px solid #27ae60">
            <div class="card-body d-flex flex-column">
                <div class="mb-3 text-center">
                    <i class="fas fa-dollar-sign fa-3x text-success"></i>
                </div>
                <h5 class="card-title text-center font-weight-bold">Financeiro</h5>
                <p class="card-text text-muted small text-center flex-grow-1">
                    Entradas (OS pagas) vs saídas (lançamentos). Saldo do período com gráfico comparativo.
                </p>
                <div class="text-center mt-2">
                    <a href="{{ route('relatorios.financeiro') }}" class="btn btn-success btn-block">
                        <i class="fas fa-arrow-right mr-1"></i> Gerar Relatório
                    </a>
                </div>
            </div>
            <div class="card-footer bg-transparent text-muted" style="font-size:.78rem">
                <i class="fas fa-filter mr-1"></i> Filtros: período (data início e fim)
                &nbsp;·&nbsp; <i class="fas fa-file-excel mr-1 text-success"></i> Excel
                &nbsp;·&nbsp; <i class="fas fa-file-pdf mr-1 text-danger"></i> PDF
            </div>
        </div>
    </div>

    {{-- Receita por Serviço --}}
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100" style="border-top:3px solid #c0392b">
            <div class="card-body d-flex flex-column">
                <div class="mb-3 text-center">
                    <i class="fas fa-wrench fa-3x text-danger"></i>
                </div>
                <h5 class="card-title text-center font-weight-bold">Receita por Serviço</h5>
                <p class="card-text text-muted small text-center flex-grow-1">
                    Quais serviços geraram mais receita. Ranking, quantidade e ticket médio por tipo de serviço.
                </p>
                <div class="text-center mt-2">
                    <a href="{{ route('relatorios.lucro-servico') }}" class="btn btn-danger btn-block">
                        <i class="fas fa-arrow-right mr-1"></i> Gerar Relatório
                    </a>
                </div>
            </div>
            <div class="card-footer bg-transparent text-muted" style="font-size:.78rem">
                <i class="fas fa-filter mr-1"></i> Filtros: período (data início e fim)
                &nbsp;·&nbsp; <i class="fas fa-file-excel mr-1 text-success"></i> Excel
                &nbsp;·&nbsp; <i class="fas fa-file-pdf mr-1 text-danger"></i> PDF
            </div>
        </div>
    </div>

    {{-- Fluxo de Caixa --}}
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100" style="border-top:3px solid #2980b9">
            <div class="card-body d-flex flex-column">
                <div class="mb-3 text-center">
                    <i class="fas fa-chart-line fa-3x text-info"></i>
                </div>
                <h5 class="card-title text-center font-weight-bold">Fluxo de Caixa</h5>
                <p class="card-text text-muted small text-center flex-grow-1">
                    Entradas e saídas mês a mês. Evolução do saldo nos últimos 3, 6 ou 12 meses.
                </p>
                <div class="text-center mt-2">
                    <a href="{{ route('relatorios.fluxo-caixa') }}" class="btn btn-info btn-block">
                        <i class="fas fa-arrow-right mr-1"></i> Gerar Relatório
                    </a>
                </div>
            </div>
            <div class="card-footer bg-transparent text-muted" style="font-size:.78rem">
                <i class="fas fa-filter mr-1"></i> Filtros: últimos 3, 6 ou 12 meses
                &nbsp;·&nbsp; <i class="fas fa-file-excel mr-1 text-success"></i> Excel
                &nbsp;·&nbsp; <i class="fas fa-file-pdf mr-1 text-danger"></i> PDF
            </div>
        </div>
    </div>

</div>

    {{-- Margem por OS --}}
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100" style="border-top:3px solid #f39c12">
            <div class="card-body d-flex flex-column">
                <div class="mb-3 text-center">
                    <i class="fas fa-percentage fa-3x text-warning"></i>
                </div>
                <h5 class="card-title text-center font-weight-bold">Margem por OS</h5>
                <p class="card-text text-muted small text-center flex-grow-1">
                    Receita recebida vs custo de peças por ordem de serviço. Margem bruta e percentual de cada OS concluída.
                </p>
                <div class="text-center mt-2">
                    <a href="{{ route('relatorios.margem-os') }}" class="btn btn-warning btn-block">
                        <i class="fas fa-arrow-right mr-1"></i> Gerar Relatório
                    </a>
                </div>
            </div>
            <div class="card-footer bg-transparent text-muted" style="font-size:.78rem">
                <i class="fas fa-filter mr-1"></i> Filtros: período (data início e fim)
            </div>
        </div>
    </div>

</div>

<div class="text-muted text-center mt-1 small">
    <i class="fas fa-info-circle mr-1"></i>
    Selecione o tipo de relatório que deseja gerar. Todos suportam exportação em Excel e PDF.
</div>

@endsection
