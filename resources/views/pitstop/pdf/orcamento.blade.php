<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }
.header { background: #c0392b; color: #fff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; }
.header h1 { font-size: 22px; font-weight: 700; letter-spacing: 1px; }
.header .sub { font-size: 10px; opacity: .85; margin-top: 2px; }
.header .empresa-info { text-align: right; font-size: 9.5px; line-height: 1.5; }
.header-logo { height: 52px; width: auto; margin-right: 14px; border-radius: 4px; background: #fff; padding: 3px; }
.doc-title { background: #f8f9fa; border-left: 4px solid #c0392b; padding: 8px 20px; margin: 0; }
.doc-title h2 { font-size: 14px; color: #c0392b; font-weight: 700; }
.doc-title .doc-info { font-size: 10px; color: #666; }
.body { padding: 16px 20px; }
.section { margin-bottom: 14px; }
.section-title { font-size: 11px; font-weight: 700; color: #c0392b; border-bottom: 1px solid #e0e0e0; padding-bottom: 4px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: .5px; }
.row2 { display: flex; gap: 16px; }
.col { flex: 1; }
.info-line { display: flex; margin-bottom: 4px; }
.info-label { font-weight: 600; color: #555; width: 90px; flex-shrink: 0; }
.info-value { color: #1a1a2e; }
table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
th { background: #f0f0f0; color: #333; font-weight: 700; padding: 6px 8px; text-align: left; border: 1px solid #ddd; }
td { padding: 5px 8px; border: 1px solid #e8e8e8; vertical-align: top; }
tr:nth-child(even) td { background: #fafafa; }
.text-right { text-align: right; }
.total-box { background: #1a1a2e; color: #fff; padding: 10px 16px; border-radius: 4px; text-align: right; margin-top: 10px; }
.total-box .label { font-size: 11px; opacity: .8; }
.total-box .value { font-size: 18px; font-weight: 700; }
.footer { position: fixed; bottom: 0; left: 0; right: 0; background: #f8f9fa; border-top: 1px solid #e0e0e0; padding: 6px 20px; display: flex; justify-content: space-between; font-size: 9px; color: #888; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
.badge-success { background: #d4edda; color: #155724; }
.badge-warning { background: #fff3cd; color: #856404; }
.badge-danger  { background: #f8d7da; color: #721c24; }
.badge-secondary { background: #e2e3e5; color: #383d41; }
</style>
</head>
<body>

<div class="header">
    <div style="display:flex;align-items:center">
        <img src="{{ 'file://' . public_path('images/logo_autofix.png') }}" class="header-logo" alt="Logo">
        <div>
            <h1>{{ $empresa['nome'] }}</h1>
            <div class="sub">{{ $empresa['endereco'] }}</div>
        </div>
    </div>
    <div class="empresa-info">
        <div><strong>CNPJ:</strong> {{ $empresa['cnpj'] }}</div>
        <div><strong>Tel:</strong> {{ $empresa['telefone'] }}</div>
        <div><strong>E-mail:</strong> {{ $empresa['email'] }}</div>
        <div>{{ $empresa['instagram'] }}</div>
    </div>
</div>

<div class="doc-title">
    <h2>ORÇAMENTO #{{ $orcamento->id }}</h2>
    <div class="doc-info">
        Emitido em: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp;
        Status:
        @php $status = str_replace('_', ' ', strtoupper($orcamento->status)); @endphp
        <span class="badge badge-{{ ['orcamento'=>'secondary','aprovado'=>'success','em_servico'=>'warning','concluido'=>'success','cancelado'=>'danger'][$orcamento->status] ?? 'secondary' }}">
            {{ $status }}
        </span>
    </div>
</div>

<div class="body">

    <div class="row2">
        <div class="col section">
            <div class="section-title">Cliente</div>
            <div class="info-line"><span class="info-label">Nome:</span><span class="info-value">{{ $orcamento->cliente->nome }}</span></div>
            @if($orcamento->cliente->telefone)
            <div class="info-line"><span class="info-label">Telefone:</span><span class="info-value">{{ $orcamento->cliente->telefone }}</span></div>
            @endif
            @if($orcamento->cliente->cpf)
            <div class="info-line"><span class="info-label">CPF:</span><span class="info-value">{{ $orcamento->cliente->cpf }}</span></div>
            @endif
        </div>
        <div class="col section">
            <div class="section-title">Veículo</div>
            <div class="info-line"><span class="info-label">Modelo:</span><span class="info-value">{{ $orcamento->veiculo->marca }} {{ $orcamento->veiculo->modelo }}</span></div>
            @if($orcamento->veiculo->placa)
            <div class="info-line"><span class="info-label">Placa:</span><span class="info-value">{{ $orcamento->veiculo->placa }}</span></div>
            @endif
            @if($orcamento->veiculo->ano)
            <div class="info-line"><span class="info-label">Ano:</span><span class="info-value">{{ $orcamento->veiculo->ano }}</span></div>
            @endif
            @if($orcamento->km_entrada)
            <div class="info-line"><span class="info-label">KM:</span><span class="info-value">{{ number_format($orcamento->km_entrada) }}</span></div>
            @endif
        </div>
    </div>

    @if($orcamento->queixa_cliente)
    <div class="section">
        <div class="section-title">Queixa do Cliente</div>
        <p>{{ $orcamento->queixa_cliente }}</p>
    </div>
    @endif

    @if($orcamento->servicos->count())
    <div class="section">
        <div class="section-title">Serviços</div>
        <table>
            <thead><tr><th>Descrição do Serviço</th><th class="text-right" width="120">Valor (R$)</th></tr></thead>
            <tbody>
                @foreach($orcamento->servicos as $s)
                <tr>
                    <td>{{ $s->servico_nome }}</td>
                    <td class="text-right">{{ number_format($s->valor, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($orcamento->pecas->count())
    <div class="section">
        <div class="section-title">Peças</div>
        <table>
            <thead><tr><th>Peça</th><th width="60">Qtd</th><th class="text-right" width="110">Unit. (R$)</th><th class="text-right" width="110">Total (R$)</th></tr></thead>
            <tbody>
                @foreach($orcamento->pecas as $p)
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

    @if($orcamento->observacao)
    <div class="section">
        <div class="section-title">Observações</div>
        <p>{{ $orcamento->observacao }}</p>
    </div>
    @endif

    <div class="total-box">
        <div class="label">TOTAL DO ORÇAMENTO</div>
        <div class="value">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</div>
    </div>

</div>

<div class="footer">
    <span>{{ $empresa['dev'] }} · {{ $empresa['email'] }}</span>
    <span>{{ $empresa['nome'] }} — {{ now()->format('d/m/Y') }}</span>
</div>

</body>
</html>
