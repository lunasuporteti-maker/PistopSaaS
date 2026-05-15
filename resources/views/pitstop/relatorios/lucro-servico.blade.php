@extends('adminlte::page')
@section('title', 'Lucro por Serviço')

@section('content_header')
    <h1>Lucro por Serviço</h1>
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
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr><th>Serviço</th><th>Quantidade</th><th>Total</th></tr>
            </thead>
            <tbody>
                @forelse($servicos as $s)
                <tr>
                    <td>{{ $s->servico_nome }}</td>
                    <td>{{ $s->quantidade }}x</td>
                    <td><strong>R$ {{ number_format($s->total, 2, ',', '.') }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-4">Nenhum serviço no período.</td></tr>
                @endforelse
            </tbody>
            @if($servicos->count())
            <tfoot class="thead-light">
                <tr>
                    <th>Total</th>
                    <th>{{ $servicos->sum('quantidade') }}x</th>
                    <th>R$ {{ number_format($servicos->sum('total'), 2, ',', '.') }}</th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
