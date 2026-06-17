@php
    $cor    = $empresa['cor'] ?? '#475569';   // cor de destaque da oficina (laranja p/ AutoFix)
    $escuro = '#2d2d2d';                        // cinza escuro (neutro, marca AutoFix)
    $fmt    = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');

    $totServicos = $orcamento->servicos->sum('valor');
    $totPecas    = $orcamento->pecas->sum(fn($p) => $p->quantidade * $p->preco_unitario);
    $totMao      = $orcamento->maoDeObra->sum('valor');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: {{ $escuro }}; }
    .page { padding-bottom: 64px; }

    /* Cabecalho */
    .header { background: {{ $escuro }}; padding: 14px 24px; }
    .header td { vertical-align: middle; }
    .header .logo { height: 46px; }
    .header .marca { color: #fff; font-size: 16px; font-weight: bold; }
    .header .right { text-align: right; }
    .header .doc { color: #fff; font-size: 21px; font-weight: bold; letter-spacing: 1px; }
    .header .doc b { color: {{ $cor }}; }
    .header .meta { color: #c8c8c8; font-size: 10px; margin-top: 3px; }
    .accent { height: 5px; background: {{ $cor }}; }
    .contato { background: #f3f3f3; padding: 5px 24px; font-size: 9px; color: #666; }

    .body { padding: 16px 24px; }

    /* Caixa cliente/veiculo */
    .infobox { width: 100%; border: 1px solid #e4e4e4; border-radius: 6px; border-collapse: separate; }
    .infobox td { padding: 8px 12px; width: 50%; vertical-align: top; }
    .infobox td.divisor { border-right: 1px solid #e4e4e4; }
    .lbl { color: {{ $cor }}; font-weight: bold; text-transform: uppercase; font-size: 8px; letter-spacing: .5px; }
    .linha { margin-bottom: 4px; }
    .linha .campo { color: #888; font-size: 9.5px; }
    .linha .dado { color: {{ $escuro }}; font-size: 11px; font-weight: bold; }

    /* Secoes de itens */
    .sec { margin-top: 14px; }
    .sec-titulo { background: {{ $cor }}; color: #fff; font-weight: bold; font-size: 10.5px;
                  text-transform: uppercase; letter-spacing: .5px; padding: 6px 10px; border-radius: 4px 4px 0 0; }
    table.itens { width: 100%; border-collapse: collapse; }
    table.itens th { background: {{ $escuro }}; color: #fff; font-size: 9px; text-transform: uppercase;
                     padding: 6px 10px; text-align: left; font-weight: bold; }
    table.itens th.r, table.itens td.r { text-align: right; }
    table.itens th.c, table.itens td.c { text-align: center; }
    table.itens td { padding: 6px 10px; border-bottom: 1px solid #eee; font-size: 10.5px; }
    table.itens tr.zebra td { background: #faf9f8; }
    table.itens tr.sub td { background: #f3f3f3; font-weight: bold; font-size: 10px; border-bottom: none; }

    /* Total */
    .total { width: 100%; margin-top: 16px; }
    .total .box { background: {{ $escuro }}; border-radius: 6px; padding: 12px 20px; }
    .total .tlabel { color: #c8c8c8; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
    .total .tvalue { color: {{ $cor }}; font-size: 22px; font-weight: bold; }

    /* Observacao + queixa */
    .nota { margin-top: 14px; border-left: 4px solid {{ $cor }}; background: #faf9f8;
            padding: 8px 12px; font-size: 10.5px; }
    .nota .nlbl { color: {{ $cor }}; font-weight: bold; text-transform: uppercase; font-size: 8.5px; letter-spacing: .5px; }

    /* Aprovacao */
    .aprov { margin-top: 26px; font-size: 10px; color: #555; }
    .aprov .linha-assinatura { border-top: 1px solid #999; width: 280px; margin-top: 34px; padding-top: 4px;
                               text-align: center; color: #888; font-size: 9px; }

    /* Rodape fixo */
    .footer { position: fixed; bottom: 0; left: 0; right: 0; background: {{ $escuro }};
              color: #fff; padding: 9px 24px; }
    .footer .tel { color: {{ $cor }}; font-weight: bold; font-size: 12px; }
    .footer .right { text-align: right; }
    .footer .nome { color: #fff; font-weight: bold; font-size: 11px; }
    .footer .slogan { color: #c8c8c8; font-size: 9px; }
    .footer .sys { color: #8a8a8a; font-size: 8px; margin-top: 2px; }
</style>
</head>
<body>

<div class="page">

    {{-- ───── Cabeçalho ───── --}}
    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" class="logo" alt="Logo">
                    @else
                        <span class="marca">{{ $empresa['nome'] }}</span>
                    @endif
                </td>
                <td class="right">
                    <div class="doc">ORÇAMENTO <b>#{{ $orcamento->id }}</b></div>
                    <div class="meta">Emitido em {{ $orcamento->created_at->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="accent"></div>
    @if($empresa['nome'] || $empresa['telefone'] || $empresa['endereco'] || $empresa['cnpj'])
    <div class="contato">
        {{ $empresa['nome'] }}@if($empresa['cnpj']) &nbsp;•&nbsp; CNPJ {{ $empresa['cnpj'] }}@endif
        @if($empresa['endereco']) &nbsp;•&nbsp; {{ $empresa['endereco'] }}@endif
        @if($empresa['telefone']) &nbsp;•&nbsp; {{ $empresa['telefone'] }}@endif
    </div>
    @endif

    <div class="body">

        {{-- ───── Cliente / Veículo ───── --}}
        <table class="infobox">
            <tr>
                <td class="divisor">
                    <div class="lbl">Cliente</div>
                    <div class="linha"><span class="dado">{{ $orcamento->cliente->nome }}</span></div>
                    @if($orcamento->cliente->telefone)
                    <div class="linha"><span class="campo">Telefone:</span> <span class="dado">{{ $orcamento->cliente->telefone }}</span></div>
                    @endif
                    @if($orcamento->cliente->cpf)
                    <div class="linha"><span class="campo">CPF:</span> <span class="dado">{{ $orcamento->cliente->cpf }}</span></div>
                    @endif
                </td>
                <td>
                    <div class="lbl">Veículo</div>
                    <div class="linha"><span class="dado">{{ $orcamento->veiculo->marca }} {{ $orcamento->veiculo->modelo }}</span></div>
                    <div class="linha">
                        @if($orcamento->veiculo->placa)<span class="campo">Placa:</span> <span class="dado">{{ $orcamento->veiculo->placa }}</span>@endif
                        @if($orcamento->veiculo->ano) &nbsp; <span class="campo">Ano:</span> <span class="dado">{{ $orcamento->veiculo->ano }}</span>@endif
                    </div>
                    @if($orcamento->km_entrada)
                    <div class="linha"><span class="campo">KM:</span> <span class="dado">{{ number_format($orcamento->km_entrada, 0, ',', '.') }}</span></div>
                    @endif
                </td>
            </tr>
        </table>

        @if($orcamento->queixa_cliente)
        <div class="nota">
            <span class="nlbl">Queixa do cliente</span><br>
            {{ $orcamento->queixa_cliente }}
        </div>
        @endif

        {{-- ───── Serviços ───── --}}
        @if($orcamento->servicos->count())
        <div class="sec">
            <div class="sec-titulo">Serviços</div>
            <table class="itens">
                <thead><tr><th>Descrição</th><th class="r" width="120">Valor</th></tr></thead>
                <tbody>
                    @foreach($orcamento->servicos as $i => $s)
                    <tr class="{{ $i % 2 ? 'zebra' : '' }}">
                        <td>{{ $s->servico_nome }}</td>
                        <td class="r">{{ $fmt($s->valor) }}</td>
                    </tr>
                    @endforeach
                    <tr class="sub"><td class="r">Subtotal de serviços</td><td class="r">{{ $fmt($totServicos) }}</td></tr>
                </tbody>
            </table>
        </div>
        @endif

        {{-- ───── Peças ───── --}}
        @if($orcamento->pecas->count())
        <div class="sec">
            <div class="sec-titulo">Peças</div>
            <table class="itens">
                <thead><tr>
                    <th>Peça</th><th class="c" width="50">Qtd</th>
                    <th class="r" width="100">Unitário</th><th class="r" width="110">Subtotal</th>
                </tr></thead>
                <tbody>
                    @foreach($orcamento->pecas as $i => $p)
                    <tr class="{{ $i % 2 ? 'zebra' : '' }}">
                        <td>{{ $p->peca->nome }}</td>
                        <td class="c">{{ $p->quantidade }}</td>
                        <td class="r">{{ $fmt($p->preco_unitario) }}</td>
                        <td class="r">{{ $fmt($p->quantidade * $p->preco_unitario) }}</td>
                    </tr>
                    @endforeach
                    <tr class="sub"><td colspan="3" class="r">Subtotal de peças</td><td class="r">{{ $fmt($totPecas) }}</td></tr>
                </tbody>
            </table>
        </div>
        @endif

        {{-- ───── Mão de obra ───── --}}
        @if($orcamento->maoDeObra->count())
        <div class="sec">
            <div class="sec-titulo">Mão de obra</div>
            <table class="itens">
                <thead><tr><th>Descrição</th><th class="r" width="120">Valor</th></tr></thead>
                <tbody>
                    @foreach($orcamento->maoDeObra as $i => $m)
                    <tr class="{{ $i % 2 ? 'zebra' : '' }}">
                        <td>{{ $m->nome_custom ?? $m->maoDeObra?->nome }}</td>
                        <td class="r">{{ $fmt($m->valor) }}</td>
                    </tr>
                    @endforeach
                    <tr class="sub"><td class="r">Subtotal de mão de obra</td><td class="r">{{ $fmt($totMao) }}</td></tr>
                </tbody>
            </table>
        </div>
        @endif

        {{-- ───── Total ───── --}}
        <table class="total">
            <tr>
                <td></td>
                <td width="240">
                    <div class="box">
                        <table width="100%"><tr>
                            <td class="tlabel">Total do orçamento</td>
                            <td class="r"><span class="tvalue">{{ $fmt($orcamento->valor_total) }}</span></td>
                        </tr></table>
                    </div>
                </td>
            </tr>
        </table>

        {{-- ───── Observação ───── --}}
        @if($orcamento->observacao)
        <div class="nota">
            <span class="nlbl">Observação</span><br>
            {{ $orcamento->observacao }}
        </div>
        @endif

        {{-- ───── Aprovação ───── --}}
        <div class="aprov">
            <div class="linha-assinatura">Aprovação do cliente</div>
        </div>

    </div>{{-- /body --}}
</div>{{-- /page --}}

{{-- ───── Rodapé fixo ───── --}}
<div class="footer">
    <table width="100%">
        <tr>
            <td>
                @if($empresa['telefone'])<span class="tel">{{ $empresa['telefone'] }}</span> &nbsp; Fale com a gente!@endif
            </td>
            <td class="right">
                <div class="nome">{{ $empresa['nome'] }}</div>
                @if($empresa['slogan'])<div class="slogan">{{ $empresa['slogan'] }}</div>@endif
                <div class="sys">Sistema por IAQueAtende</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
