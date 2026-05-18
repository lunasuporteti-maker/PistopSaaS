<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; padding: 20px; }
    h1 { font-size: 18px; color: #c0392b; margin-bottom: 4px; }
    .periodo { color: #666; font-size: 11px; margin-bottom: 20px; }
    .cards { display: table; width: 100%; margin-bottom: 20px; border-collapse: separate; border-spacing: 10px; }
    .card { display: table-cell; width: 33%; padding: 14px; border-radius: 6px; text-align: center; }
    .card-e { background: #d4edda; border: 1px solid #27ae60; }
    .card-s { background: #f8d7da; border: 1px solid #c0392b; }
    .card-r { background: #d1ecf1; border: 1px solid #17a2b8; }
    .card-rn { background: #fff3cd; border: 1px solid #f39c12; }
    .card-val { font-size: 20px; font-weight: 800; }
    .card-label { font-size: 10px; color: #555; margin-top: 4px; }
    hr { border: none; border-top: 1px solid #ddd; margin: 16px 0; }
    .footer { color: #aaa; font-size: 9px; text-align: center; margin-top: 30px; }
</style>
</head>
<body>
<h1>Relatório Financeiro</h1>
<div class="periodo">Período: {{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }}</div>
@php $lucro = $entradas - $saidas; @endphp

<table width="100%" cellspacing="8">
    <tr>
        <td width="33%" style="background:#d4edda; border:1px solid #27ae60; border-radius:6px; padding:14px; text-align:center;">
            <div style="font-size:18px; font-weight:800; color:#27ae60;">R$ {{ number_format($entradas, 2, ',', '.') }}</div>
            <div style="font-size:10px; color:#555; margin-top:4px;">Entradas (OS pagas)</div>
        </td>
        <td width="33%" style="background:#f8d7da; border:1px solid #c0392b; border-radius:6px; padding:14px; text-align:center;">
            <div style="font-size:18px; font-weight:800; color:#c0392b;">R$ {{ number_format($saidas, 2, ',', '.') }}</div>
            <div style="font-size:10px; color:#555; margin-top:4px;">Saídas (lançamentos)</div>
        </td>
        <td width="33%" style="background:{{ $lucro >= 0 ? '#d1ecf1' : '#fff3cd' }}; border:1px solid {{ $lucro >= 0 ? '#17a2b8' : '#f39c12' }}; border-radius:6px; padding:14px; text-align:center;">
            <div style="font-size:18px; font-weight:800; color:{{ $lucro >= 0 ? '#17a2b8' : '#f39c12' }};">
                {{ $lucro >= 0 ? '+' : '' }}R$ {{ number_format($lucro, 2, ',', '.') }}
            </div>
            <div style="font-size:10px; color:#555; margin-top:4px;">{{ $lucro >= 0 ? 'Saldo positivo' : 'Saldo negativo' }}</div>
        </td>
    </tr>
</table>

<div class="footer">Gerado em {{ now()->format('d/m/Y H:i') }} — PitStop</div>
</body>
</html>
