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
    .text-right  { text-align: right; }
    .text-center { text-align: center; }
    .text-success { color: #27ae60; }
    .footer { color: #aaa; font-size: 9px; text-align: center; margin-top: 30px; }
    tfoot td { font-weight: bold; background: #f9f9f9; border-top: 2px solid #ddd; }
</style>
</head>
<body>
<h1>Receita por Serviço</h1>
<div class="periodo">Período: {{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }}</div>

<table>
    <thead>
        <tr>
            <th>Serviço</th>
            <th class="text-center">Qtd</th>
            <th class="text-right">Receita Total</th>
            <th class="text-right">Ticket Médio</th>
        </tr>
    </thead>
    <tbody>
        @forelse($servicos as $s)
        <tr>
            <td>{{ $s->servico_nome }}</td>
            <td class="text-center">{{ $s->quantidade }}x</td>
            <td class="text-right text-success">R$ {{ number_format($s->total, 2, ',', '.') }}</td>
            <td class="text-right">R$ {{ number_format($s->total / $s->quantidade, 2, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="4" style="text-align:center; color:#999; padding:16px;">Nenhum serviço concluído no período.</td></tr>
        @endforelse
    </tbody>
    @if($servicos->count())
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td class="text-center">{{ $servicos->sum('quantidade') }}x</td>
            <td class="text-right text-success">R$ {{ number_format($servicos->sum('total'), 2, ',', '.') }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="footer">Gerado em {{ now()->format('d/m/Y H:i') }} — PitStop</div>
</body>
</html>
