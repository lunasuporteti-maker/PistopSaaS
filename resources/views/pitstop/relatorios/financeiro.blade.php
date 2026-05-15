@extends('adminlte::page')
@section('title', 'Relatório Financeiro')

@section('content_header')
    <h1>Relatório Financeiro</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <div class="form-group mr-3">
                <label class="mr-2">De:</label>
                <input type="date" name="inicio" class="form-control" value="{{ $inicio->toDateString() }}">
            </div>
            <div class="form-group mr-3">
                <label class="mr-2">Até:</label>
                <input type="date" name="fim" class="form-control" value="{{ $fim->toDateString() }}">
            </div>
            <button class="btn btn-danger"><i class="fas fa-search"></i> Gerar</button>
        </form>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Entradas</span>
                        <span class="info-box-number">R$ {{ number_format($entradas, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Saídas</span>
                        <span class="info-box-number">R$ {{ number_format($saidas, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-{{ ($entradas - $saidas) >= 0 ? 'info' : 'warning' }}">
                    <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Lucro / Prejuízo</span>
                        <span class="info-box-number">R$ {{ number_format($entradas - $saidas, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <p class="text-muted text-center mt-2">Período: {{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }}</p>
    </div>
</div>
@endsection
