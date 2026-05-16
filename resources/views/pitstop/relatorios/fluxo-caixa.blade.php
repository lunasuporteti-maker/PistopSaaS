@extends('adminlte::page')
@section('title', 'Fluxo de Caixa')

@section('content_header')
    <h1>Fluxo de Caixa</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center" style="gap:12px">
        <form method="GET" class="form-inline">
            <label class="mr-2">Últimos:</label>
            <select name="meses" class="form-control mr-2" onchange="this.form.submit()">
                @foreach([3,6,12] as $m)
                <option value="{{ $m }}" {{ $meses == $m ? 'selected' : '' }}>{{ $m }} meses</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('relatorios.fluxo-caixa.export', ['meses' => $meses]) }}"
           class="btn btn-sm btn-success">
            <i class="fas fa-file-excel mr-1"></i>Excel
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="thead-light">
                <tr><th>Mês</th><th>Entradas</th><th>Saídas</th><th>Saldo</th></tr>
            </thead>
            <tbody>
                @php $mesesLista = collect($entradas->keys()->merge($saidas->keys())->unique()->sort()->values()); @endphp
                @foreach($mesesLista as $mes)
                @php
                    $e = $entradas[$mes] ?? 0;
                    $s = $saidas[$mes] ?? 0;
                    $saldo = $e - $s;
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $mes)->format('M/Y') }}</td>
                    <td class="text-success">R$ {{ number_format($e, 2, ',', '.') }}</td>
                    <td class="text-danger">R$ {{ number_format($s, 2, ',', '.') }}</td>
                    <td class="{{ $saldo >= 0 ? 'text-success' : 'text-danger' }} font-weight-bold">
                        R$ {{ number_format($saldo, 2, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
