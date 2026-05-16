<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acompanhe seu Serviço — AutoFix</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 100%); min-height: 100vh; font-family: 'Segoe UI', sans-serif; color: #fff; }
        .container { max-width: 600px; padding: 32px 16px; }
        .logo-box { text-align: center; margin-bottom: 28px; }
        .logo-box .icon { width: 64px; height: 64px; background: linear-gradient(135deg,#c0392b,#e74c3c); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; box-shadow: 0 8px 24px rgba(192,57,43,.4); }
        .logo-box h1 { font-size: 1.8rem; font-weight: 800; margin-top: 12px; }
        .logo-box p { color: rgba(255,255,255,.5); font-size: .85rem; }
        .status-card { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12); border-radius: 16px; padding: 28px 24px; margin-bottom: 20px; text-align: center; }
        .status-icon { font-size: 3.5rem; margin-bottom: 12px; }
        .status-title { font-size: 1.4rem; font-weight: 800; margin-bottom: 8px; }
        .status-desc { color: rgba(255,255,255,.65); font-size: .9rem; line-height: 1.6; }
        /* Progress steps */
        .steps { display: flex; justify-content: space-between; position: relative; margin-bottom: 28px; }
        .steps::before { content:''; position:absolute; top:18px; left:10%; right:10%; height:3px; background:rgba(255,255,255,.1); z-index:0; }
        .step { text-align: center; flex: 1; position: relative; z-index: 1; }
        .step-dot { width: 36px; height: 36px; border-radius: 50%; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; transition: all .3s; }
        .step-dot.done { background: #28a745; color: #fff; }
        .step-dot.active { background: linear-gradient(135deg,#c0392b,#e74c3c); color: #fff; box-shadow: 0 0 0 4px rgba(192,57,43,.3); }
        .step-dot.pending { background: rgba(255,255,255,.1); color: rgba(255,255,255,.4); }
        .step-label { font-size: .7rem; color: rgba(255,255,255,.5); }
        .step-label.active-label { color: #fff; font-weight: 700; }
        /* Info card */
        .info-card { background: rgba(255,255,255,.05); border-radius: 12px; padding: 16px 20px; margin-bottom: 16px; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,.06); }
        .info-row:last-child { border: none; }
        .info-label { color: rgba(255,255,255,.5); font-size: .85rem; }
        .info-value { font-weight: 600; font-size: .85rem; }
        .footer-txt { text-align: center; color: rgba(255,255,255,.25); font-size: .75rem; margin-top: 24px; }
        .refresh-btn { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15); color: rgba(255,255,255,.7); border-radius: 8px; padding: 8px 20px; font-size: .82rem; cursor: pointer; transition: all .2s; }
        .refresh-btn:hover { background: rgba(255,255,255,.15); color: #fff; }
    </style>
</head>
<body>
<div class="container mx-auto">

    <div class="logo-box">
        <div class="icon"><i class="fas fa-wrench"></i></div>
        <h1>AutoFix</h1>
        <p>Acompanhe o andamento do seu serviço</p>
    </div>

    {{-- Progress steps --}}
    @php $ordem = ['orcamento','aprovado','em_servico','concluido']; @endphp
    <div class="steps">
        @foreach($ordem as $i => $step)
        @php
            $past   = $posAtual !== false && $i < $posAtual;
            $active = $posAtual !== false && $i === $posAtual;
            $labels = ['Orçamento','Aprovado','Em Serviço','Concluído'];
        @endphp
        <div class="step">
            <div class="step-dot {{ $past ? 'done' : ($active ? 'active' : 'pending') }}">
                @if($past) <i class="fas fa-check"></i>
                @else {{ $i + 1 }}
                @endif
            </div>
            <div class="step-label {{ $active ? 'active-label' : '' }}">{{ $labels[$i] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Status atual --}}
    <div class="status-card" data-status="{{ $orcamento->status }}" style="border-color: {{ $etapa['cor'] }}40">
        <div class="status-icon">{{ $etapa['icone'] }}</div>
        <div class="status-title">{{ $etapa['titulo'] }}</div>
        <div class="status-desc">{{ $etapa['desc'] }}</div>
    </div>

    {{-- Informações do serviço --}}
    <div class="info-card">
        <div class="info-row">
            <span class="info-label">Orçamento</span>
            <span class="info-value">#{{ $orcamento->id }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Veículo</span>
            <span class="info-value">{{ $orcamento->veiculo->marca }} {{ $orcamento->veiculo->modelo }}
                @if($orcamento->veiculo->placa) · {{ $orcamento->veiculo->placa }}@endif
            </span>
        </div>
        @if($orcamento->servicos->count())
        <div class="info-row">
            <span class="info-label">Serviços</span>
            <span class="info-value">{{ $orcamento->servicos->pluck('servico_nome')->implode(', ') }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Total</span>
            <span class="info-value" style="color:#4ade80">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Atualizado em</span>
            <span class="info-value">{{ $orcamento->updated_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <div class="text-center">
        <button class="refresh-btn" onclick="location.reload()">
            <i class="fas fa-sync-alt mr-1"></i> Atualizar status
        </button>
    </div>

    <div class="footer-txt">
        <p>AutoFix · (84) 99672-2453 · AutoFix.atendimento@gmail.com</p>
        <p style="margin-top:4px">Sistema desenvolvido por IAQueAtende</p>
        <p id="refresh-timer" style="margin-top:8px;color:rgba(255,255,255,.2);font-size:.7rem">
            Atualizando em <span id="countdown">30</span>s
        </p>
    </div>

</div>

<script>
(function () {
    var statusAtual = '{{ $orcamento->status }}';
    var intervalo   = 30; // segundos
    var conta       = intervalo;
    var el          = document.getElementById('countdown');

    // Contagem regressiva visual
    var timer = setInterval(function () {
        conta--;
        if (el) el.textContent = conta;
        if (conta <= 0) {
            conta = intervalo;
            verificarStatus();
        }
    }, 1000);

    function verificarStatus() {
        // cache-busting via timestamp — evita que mobile sirva versao antiga
        var url = window.location.href.split('?')[0] + '?_t=' + Date.now();
        fetch(url, {
            cache: 'no-store',
            headers: { 'X-Requested-With': 'fetch', 'Cache-Control': 'no-cache' }
        })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                var match = html.match(/data-status="([^"]+)"/);
                if (match && match[1] !== statusAtual) {
                    document.body.style.opacity = '0';
                    document.body.style.transition = 'opacity .4s';
                    setTimeout(function () { location.reload(); }, 400);
                }
            })
            .catch(function () {});
    }
})();
</script>
</body>
</html>
