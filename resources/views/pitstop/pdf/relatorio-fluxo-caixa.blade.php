<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; padding: 20px; }
    h1 { font-size: 18px; color: #c0392b; margin-bottom: 4px; }
    .periodo { color: #666; font-size: 11px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f0f0f0; padding: 8px 10px; text-align: left; font-size: 11px; border-bottom: 2px solid #ddd; }
    td { padding: 7px 10px; border-bottom: 1px solid #eee; font-size: 11px; }
    .text-success { color: #27ae60; }
    .text-danger  { color: #c0392b; }
    .text-info    { color: #17a2b8; }
    .footer { color: #aaa; font-size: 9px; text-align: center; margin-top: 30px; }
    tfoot td { font-weight: bold; background: #f9f9f9; border-top: 2px solid #ddd; }
</style>
</head>
<body>
<h1>Fluxo de Caixa</h1>
<div class="periodo">Últimos {{ $meses }} meses — Gerado em {{ now()->format('d/m/Y H:i') }}</div>

@php
    $mesesLista = collect($entradas->keys()->merge($saidas->keys())->unique()->sort()->values());
    $totalE = 0; $totalS = 0;
@endphp

<table>
    <thead>
        <tr><th>Mês</th><th>Entradas</th><th>Saídas</th><th>Saldo</th></tr>
    </thead>
    <tbody>
        @foreach($mesesLista as $mes)
        @php
            $e = (float)($entradas[$mes] ?? 0);
            $s = (float)($saidas[$mes] ?? 0);
            $saldo = $e - $s;
            $totalE += $e; $totalS += $s;
        @endphp
        <tr>
            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $mes)->format('M/Y') }}</td>
            <td class="text-success">R$ {{ number_format($e, 2, ',', '.') }}</td>
            <td class="text-danger">R$ {{ number_format($s, 2, ',', '.') }}</td>
            <td class="{{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">R$ {{ number_format($saldo, 2, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td class="text-success">R$ {{ number_format($totalE, 2, ',', '.') }}</td>
            <td class="text-danger">R$ {{ number_format($totalS, 2, ',', '.') }}</td>
            <td class="{{ ($totalE - $totalS) >= 0 ? 'text-success' : 'text-danger' }}">R$ {{ number_format($totalE - $totalS, 2, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

<div class="footer">PitStop</div>
</body>
</html>
