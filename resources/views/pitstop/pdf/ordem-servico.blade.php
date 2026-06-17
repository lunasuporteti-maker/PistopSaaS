<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }
.header { background: #1a1a2e; color: #fff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; }
.header h1 { font-size: 20px; font-weight: 700; }
.header .os-num { font-size: 26px; font-weight: 800; color: #e74c3c; }
.header .empresa-info { text-align: right; font-size: 9.5px; line-height: 1.6; }
.header-logo { height: 52px; width: auto; margin-right: 14px; border-radius: 4px; background: #fff; padding: 3px; }
.body { padding: 16px 20px; }
.row2 { display: flex; gap: 14px; }
.col { flex: 1; }
.section { margin-bottom: 14px; }
.section-title { font-size: 11px; font-weight: 700; color: #1a1a2e; background: #f0f0f0; padding: 5px 8px; margin-bottom: 8px; border-left: 3px solid #c0392b; }
.info-line { display: flex; margin-bottom: 3px; }
.info-label { font-weight: 600; color: #555; width: 90px; flex-shrink: 0; font-size: 10px; }
.info-value { color: #1a1a2e; }
table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
th { background: #1a1a2e; color: #fff; padding: 6px 8px; text-align: left; }
td { padding: 5px 8px; border-bottom: 1px solid #eee; }
tr:nth-child(even) td { background: #fafafa; }
.text-right { text-align: right; }
.total-row td { font-weight: 700; background: #f8f8f8 !important; font-size: 12px; }
.assinatura { margin-top: 30px; display: flex; gap: 40px; }
.assinatura-box { flex: 1; text-align: center; }
.assinatura-line { border-top: 1px solid #333; margin-bottom: 4px; margin-top: 40px; }
.footer { position: fixed; bottom: 0; left: 0; right: 0; background: #f8f9fa; border-top: 1px solid #e0e0e0; padding: 5px 20px; display: flex; justify-content: space-between; font-size: 9px; color: #888; }
</style>
</head>
<body>

<div class="header">
    <div style="display:flex;align-items:center">
        @if(!empty($logoBase64))
        <img src="{{ $logoBase64 }}" class="header-logo" alt="Logo">
        @endif
        <div>
        <h1>{{ $empresa['nome'] }}</h1>
        <div style="font-size:9px;opacity:.8;margin-top:2px">{{ $empresa['endereco'] }}</div>
        <div style="margin-top:6px">
            <span style="font-size:10px;opacity:.7">Ordem de Serviço</span>
            <div class="os-num">{{ $ordem->numero_os }}</div>
        </div>
        </div>{{-- /inner --}}
    </div>
    <div class="empresa-info">
        <div><strong>CNPJ:</strong> {{ $empresa['cnpj'] }}</div>
        <div><strong>Tel:</strong> {{ $empresa['telefone'] }}</div>
        <div><strong>E-mail:</strong> {{ $empresa['email'] }}</div>
        <div>{{ $empresa['instagram'] }}</div>
        <div style="margin-top:6px;font-size:9px;opacity:.7">{{ now()->format('d/m/Y H:i') }}</div>
    </div>
</div>

<div class="body">

    <div class="row2">
        <div class="col section">
            <div class="section-title">DADOS DO CLIENTE</div>
            <div class="info-line"><span class="info-label">Nome:</span><span class="info-value">{{ $ordem->cliente->nome }}</span></div>
            @if($ordem->cliente->telefone)
            <div class="info-line"><span class="info-label">Telefone:</span><span class="info-value">{{ $ordem->cliente->telefone }}</span></div>
            @endif
            @if($ordem->cliente->cpf)
            <div class="info-line"><span class="info-label">CPF:</span><span class="info-value">{{ $ordem->cliente->cpf }}</span></div>
            @endif
        </div>
        <div class="col section">
            <div class="section-title">DADOS DO VEÍCULO</div>
            <div class="info-line"><span class="info-label">Modelo:</span><span class="info-value">{{ $ordem->veiculo->marca }} {{ $ordem->veiculo->modelo }}</span></div>
            @if($ordem->veiculo->placa)
            <div class="info-line"><span class="info-label">Placa:</span><span class="info-value">{{ $ordem->veiculo->placa }}</span></div>
            @endif
            @if($ordem->veiculo->ano)
            <div class="info-line"><span class="info-label">Ano:</span><span class="info-value">{{ $ordem->veiculo->ano }}</span></div>
            @endif
            @if($ordem->veiculo->cor)
            <div class="info-line"><span class="info-label">Cor:</span><span class="info-value">{{ $ordem->veiculo->cor }}</span></div>
            @endif
        </div>
    </div>

    @if($ordem->descricao)
    <div class="section">
        <div class="section-title">DESCRIÇÃO DO SERVIÇO</div>
        <p>{{ $ordem->descricao }}</p>
    </div>
    @endif

    @if($ordem->orcamento && $ordem->orcamento->servicos->count())
    <div class="section">
        <div class="section-title">SERVIÇOS REALIZADOS</div>
        <table>
            <thead><tr><th>Descrição</th><th class="text-right" width="120">Valor (R$)</th></tr></thead>
            <tbody>
                @foreach($ordem->orcamento->servicos as $s)
                <tr>
                    <td>{{ $s->servico_nome }}</td>
                    <td class="text-right">{{ number_format($s->valor, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>TOTAL DOS SERVIÇOS</td>
                    <td class="text-right">R$ {{ number_format($ordem->orcamento->servicos->sum('valor'), 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    @if($ordem->pecas->count())
    <div class="section">
        <div class="section-title">PEÇAS UTILIZADAS</div>
        <table>
            <thead><tr><th>Peça</th><th width="60">Qtd</th><th class="text-right" width="110">Unit. (R$)</th><th class="text-right" width="110">Total (R$)</th></tr></thead>
            <tbody>
                @foreach($ordem->pecas as $p)
                <tr>
                    <td>{{ $p->peca->nome }}</td>
                    <td>{{ $p->quantidade }}</td>
                    <td class="text-right">{{ number_format($p->preco_unitario, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($p->quantidade * $p->preco_unitario, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div style="text-align:right;margin-top:10px;padding:10px 16px;background:#1a1a2e;color:#fff;border-radius:4px;">
        <div style="font-size:10px;opacity:.8">TOTAL DA ORDEM DE SERVIÇO</div>
        <div style="font-size:20px;font-weight:800;">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</div>
        @if($ordem->garantia_dias)
        <div style="font-size:10px;opacity:.7;margin-top:2px">Garantia: {{ $ordem->garantia_dias }} dias</div>
        @endif
    </div>

    <div class="assinatura">
        <div class="assinatura-box">
            <div class="assinatura-line"></div>
            <div>Assinatura do Cliente</div>
            <div style="font-size:9px;color:#666;margin-top:2px">{{ $ordem->cliente->nome }}</div>
        </div>
        <div class="assinatura-box">
            <div class="assinatura-line"></div>
            <div>{{ $empresa['nome'] }}</div>
            <div style="font-size:9px;color:#666;margin-top:2px">Responsável</div>
        </div>
    </div>

</div>

<div class="footer">
    <span>Sistema desenvolvido por <a href="https://iaqueatende.com.br/" style="color:#c0392b;text-decoration:none">IAQueAtende</a></span>
    <span>{{ $empresa['nome'] }} · {{ $empresa['telefone'] }} · {{ $empresa['email'] }}</span>
</div>

</body>
</html>
