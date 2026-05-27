<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kanban — PitStop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { background: #1a1a2e; font-family: 'Segoe UI', sans-serif; min-height: 100vh; }

        .kanban-header {
            background: linear-gradient(135deg, #c0392b, #922b21);
            padding: 12px 20px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.4);
        }
        .kanban-header h1 { color: #fff; font-size: 1.4rem; margin: 0; font-weight: 700; letter-spacing: 1px; }
        .kanban-header .badge-count { background: rgba(255,255,255,0.2); color: #fff; padding: 3px 10px; border-radius: 12px; font-size: .85rem; }

        .kanban-board { display: flex; gap: 14px; padding: 16px; overflow-x: auto; height: calc(100vh - 58px); }

        .kanban-col { min-width: 280px; max-width: 300px; flex-shrink: 0; background: #16213e; border-radius: 12px; display: flex; flex-direction: column; max-height: 100%; }
        .col-header { padding: 12px 14px 10px; border-radius: 12px 12px 0 0; display: flex; align-items: center; justify-content: space-between; }
        .col-header h3 { color: #fff; font-size: .95rem; font-weight: 700; margin: 0; }
        .col-count { background: rgba(255,255,255,0.25); color: #fff; font-size: .8rem; padding: 2px 8px; border-radius: 10px; font-weight: 700; }

        .col-body { padding: 10px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 10px; }
        .col-body::-webkit-scrollbar { width: 4px; }
        .col-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }

        .kanban-card {
            background: #0f3460; border-radius: 10px; padding: 12px; cursor: grab;
            border-left: 4px solid transparent; transition: transform .15s, box-shadow .15s, opacity .15s; position: relative;
        }
        .kanban-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.4); }
        .kanban-card.sortable-ghost { opacity: 0.35; transform: scale(.97); }
        .kanban-card.sortable-chosen { cursor: grabbing; box-shadow: 0 10px 30px rgba(0,0,0,0.6); }
        .kanban-card.saving { opacity: 0.6; }

        .card-cliente { color: #e2e8f0; font-weight: 700; font-size: .9rem; margin-bottom: 3px; }
        .card-veiculo { color: #94a3b8; font-size: .8rem; margin-bottom: 6px; }
        .card-valor { color: #4ade80; font-size: .85rem; font-weight: 600; }
        .card-data { color: #64748b; font-size: .75rem; }
        .card-queixa { color: #94a3b8; font-size: .75rem; margin-top: 6px; font-style: italic; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .card-actions { display: flex; gap: 5px; margin-top: 10px; flex-wrap: wrap; }

        .btn-wa { background: #25d366; color: #fff; border: none; border-radius: 6px; padding: 4px 10px; font-size: .78rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 5px; text-decoration: none; transition: background .2s; }
        .btn-wa:hover { background: #1da851; color: #fff; text-decoration: none; }
        .btn-ver { background: rgba(255,255,255,0.1); color: #94a3b8; border: none; border-radius: 6px; padding: 4px 10px; font-size: .78rem; cursor: pointer; text-decoration: none; transition: background .2s; }
        .btn-ver:hover { background: rgba(255,255,255,0.2); color: #fff; text-decoration: none; }
        .btn-iniciar { background: rgba(59,130,246,.25); color: #93c5fd; border: none; border-radius: 6px; padding: 4px 10px; font-size: .78rem; font-weight: 600; cursor: pointer; transition: all .2s; display: flex; align-items: center; gap: 4px; }
        .btn-iniciar:hover { background: rgba(59,130,246,.5); color: #fff; }
        .btn-iniciar:disabled { opacity: .5; cursor: not-allowed; }
        .btn-andamento { background: rgba(234,179,8,.2); color: #fbbf24; border: none; border-radius: 6px; padding: 4px 10px; font-size: .78rem; cursor: pointer; transition: all .2s; display: flex; align-items: center; gap: 4px; }
        .btn-andamento:hover { background: rgba(234,179,8,.4); color: #fde047; }
        .btn-finalizar { background: rgba(34,197,94,.25); color: #86efac; border: none; border-radius: 6px; padding: 4px 10px; font-size: .78rem; font-weight: 600; cursor: pointer; transition: all .2s; display: flex; align-items: center; gap: 4px; }
        .btn-finalizar:hover { background: rgba(34,197,94,.5); color: #fff; }
        .btn-finalizar:disabled { opacity: .5; cursor: not-allowed; }
        .btn-arquivar { background: rgba(100,116,139,.2); color: #64748b; border: none; border-radius: 6px; padding: 4px 8px; font-size: .75rem; cursor: pointer; transition: all .2s; }
        .btn-arquivar:hover { background: rgba(100,116,139,.4); color: #e2e8f0; }

        #toast { position: fixed; bottom: 20px; right: 20px; z-index: 9999; background: #1e293b; color: #e2e8f0; padding: 10px 18px; border-radius: 8px; font-size: .85rem; border-left: 4px solid #4ade80; box-shadow: 0 4px 20px rgba(0,0,0,0.5); opacity: 0; transition: opacity .3s; pointer-events: none; }
        #toast.show { opacity: 1; }
        #toast.erro { border-color: #f87171; }
        .empty-col { color: #334155; text-align: center; padding: 20px 10px; font-size: .85rem; }
        @media (max-width: 600px) { .kanban-col { min-width: 240px; } }

        /* Modal pagamento */
        .pagamento-row { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
        .pagamento-row select, .pagamento-row input { flex: 1; }
        .btn-remove-pag { background: none; border: none; color: #dc3545; font-size: 1.1rem; cursor: pointer; padding: 0 4px; }
    </style>
</head>
<body>

<div class="kanban-header">
    <h1><i class="fas fa-columns mr-2"></i>PitStop — Painel Kanban</h1>
    <div class="d-flex align-items-center gap-2">
        <span class="badge-count" id="totalCards">{{ $cards->flatten()->count() }} cards</span>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-light ml-3" title="Voltar ao Dashboard">
            <i class="fas fa-home"></i>
        </a>
    </div>
</div>

<div class="kanban-board" id="kanbanBoard">
    @foreach($colunas as $status => $coluna)
    @php $itens = $cards->get($status, collect()); @endphp
    <div class="kanban-col" data-status="{{ $status }}">
        <div class="col-header" style="background: {{ $coluna['cor'] }}">
            <h3>{{ $coluna['label'] }}</h3>
            <span class="col-count">{{ $itens->count() }}</span>
        </div>
        <div class="col-body kanban-lista" data-status="{{ $status }}">
            @forelse($itens as $orc)
            @php
                $nomeCliente = $orc->cliente->nome ?? 'Cliente';
                $nomeVeiculo = trim(($orc->veiculo->marca ?? '') . ' ' . ($orc->veiculo->modelo ?? ''));
                $telefone    = preg_replace('/\D/', '', $orc->cliente->telefone ?? '');
                $linkPublico = $orc->token_publico ? url('/acompanhar/' . $orc->token_publico) : null;
                $msg = str_replace(['{nome}','{veiculo}','{link}'], [$nomeCliente, $nomeVeiculo ?: 'veiculo', $linkPublico ?? ''], $mensagens[$status]);
                $waUrl = $telefone ? 'https://wa.me/55' . $telefone . '?text=' . rawurlencode($msg) : null;
            @endphp
            <div class="kanban-card"
                 data-id="{{ $orc->id }}"
                 data-status="{{ $status }}"
                 data-token="{{ $orc->token_publico }}"
                 data-valor="{{ $orc->valor_total }}"
                 data-cliente="{{ $nomeCliente }}"
                 data-telefone="{{ $telefone }}"
                 draggable="true">
                <div class="card-cliente">{{ $nomeCliente }}</div>
                <div class="card-veiculo">
                    <i class="fas fa-car"></i> {{ $nomeVeiculo ?: '—' }}
                    @if($orc->veiculo->placa ?? null)<span style="color:#475569"> · {{ $orc->veiculo->placa }}</span>@endif
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="card-valor">R$ {{ number_format($orc->valor_total, 2, ',', '.') }}</span>
                    <span class="card-data">{{ $orc->created_at->format('d/m') }}</span>
                </div>
                @if($orc->queixa_cliente)
                <div class="card-queixa" title="{{ $orc->queixa_cliente }}">"{{ $orc->queixa_cliente }}"</div>
                @endif
                <div class="card-actions">
                    @if($waUrl)
                    <a href="{{ $waUrl }}" target="_blank" class="btn-wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    @endif
                    <a href="/orcamentos/{{ $orc->id }}" target="_blank" class="btn-ver"><i class="fas fa-eye"></i> Ver</a>
                    @if($linkPublico)
                    <a href="{{ $linkPublico }}" target="_blank" class="btn-ver" title="Link do cliente"><i class="fas fa-share-alt"></i></a>
                    @endif
                    @if($status === 'aprovado')
                    <button class="btn-iniciar" data-id="{{ $orc->id }}"><i class="fas fa-play"></i> Iniciar</button>
                    @endif
                    @if($status === 'em_servico')
                    <button class="btn-andamento" data-id="{{ $orc->id }}" data-andamento="{{ $orc->andamento ?? '' }}"><i class="fas fa-pencil-alt"></i> Andamento</button>
                    <button class="btn-finalizar" data-id="{{ $orc->id }}" data-valor="{{ $orc->valor_total }}"><i class="fas fa-check-circle"></i> Finalizar</button>
                    @endif
                    @if($status === 'concluido')
                    <button class="btn-arquivar" data-id="{{ $orc->id }}" title="Arquivar"><i class="fas fa-archive"></i></button>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-col"><i class="fas fa-inbox mb-1 d-block" style="font-size:1.5rem"></i>Vazio</div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

<div id="toast"></div>

{{-- Modal: Pagamento + Gerar OS --}}
<div class="modal fade" id="modalPagamento" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content" style="background:#1e293b; color:#e2e8f0; border:1px solid #334155;">
            <div class="modal-header" style="background:#166534; border-bottom:1px solid #334155;">
                <h5 class="modal-title text-white"><i class="fas fa-check-circle mr-2"></i>Finalizar Serviço — Pagamento</h5>
                <button type="button" class="close text-white" id="btnCancelarPagamento"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" id="infoPagamento"></p>
                <label class="font-weight-bold mb-2">Formas de Pagamento</label>
                <div id="listaPagamentos"></div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnAddPagamento">
                    <i class="fas fa-plus mr-1"></i> Adicionar forma
                </button>
                <hr style="border-color:#334155">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Total informado:</span>
                    <strong id="totalPagamentos" class="text-success" style="font-size:1.2rem">R$ 0,00</strong>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #334155;">
                <button type="button" class="btn btn-outline-secondary" id="btnCancelarPagamento2">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarPagamento">
                    <i class="fas fa-check mr-1"></i> Confirmar e Gerar OS
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Resultado OS gerada --}}
<div class="modal fade" id="modalOsGerada" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#1e293b; color:#e2e8f0; border:1px solid #334155;">
            <div class="modal-header" style="background:#1e3a5f; border-bottom:1px solid #334155;">
                <h5 class="modal-title text-white"><i class="fas fa-file-alt mr-2"></i>OS Gerada com Sucesso</h5>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success" style="font-size:3rem"></i>
                <h4 class="mt-3 text-white" id="osNumero"></h4>
                <p class="text-muted" id="osValor"></p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="#" id="btnImprimirOs" target="_blank" class="btn btn-danger">
                        <i class="fas fa-print mr-1"></i> Imprimir OS
                    </a>
                    <a href="#" id="btnWaOs" target="_blank" class="btn" style="background:#25d366; color:#fff;">
                        <i class="fab fa-whatsapp mr-1"></i> Enviar WhatsApp
                    </a>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #334155;">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="btnFecharOs">Fechar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Andamento do Serviço --}}
<div class="modal fade" id="modalAndamento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#1e293b; color:#e2e8f0; border:1px solid #334155;">
            <div class="modal-header" style="background:#713f12; border-bottom:1px solid #334155;">
                <h5 class="modal-title text-white"><i class="fas fa-pencil-alt mr-2"></i>Registrar Andamento</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Registre o que foi feito, peças trocadas e observações. Este campo é opcional.</p>
                <textarea id="textoAndamento" class="form-control" rows="5"
                          style="background:#0f172a; color:#e2e8f0; border-color:#334155;"
                          placeholder="Ex: Trocado filtro de óleo, verificado sistema de freios, identificado desgaste nas pastilhas..."></textarea>
            </div>
            <div class="modal-footer" style="border-top:1px solid #334155;">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning text-dark" id="btnSalvarAndamento">
                    <i class="fas fa-save mr-1"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let pagamentoOrcId   = null; // ID do orçamento sendo concluído
let pagamentoCardEl  = null; // elemento DOM do card
let pagamentoOrigCol = null; // coluna original (para rollback)
let andamentoOrcId   = null;

// ── Toast ─────────────────────────────────────────────────────
function toast(msg, erro = false) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className   = 'show' + (erro ? ' erro' : '');
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => el.className = '', 2800);
}

// ── Contagem de cards ─────────────────────────────────────────
function updateCount() {
    let total = 0;
    document.querySelectorAll('.kanban-lista').forEach(lista => {
        const n = lista.querySelectorAll('.kanban-card').length;
        total  += n;
        const col = lista.closest('.kanban-col');
        const cnt = col.querySelector('.col-count');
        if (cnt) cnt.textContent = n;
    });
    document.getElementById('totalCards').textContent = total + ' cards';
}

// ── Auto-refresh: verifica mudanças a cada 30s ─────────────────
var hashAtual = null;
function verificarEstado() {
    fetch('/kanban/estado', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            if (hashAtual === null) {
                hashAtual = data.hash;
            } else if (data.hash !== hashAtual) {
                var scrollX = window.scrollX;
                sessionStorage.setItem('kanban_scroll', scrollX);
                location.reload();
            }
        }).catch(() => {});
}
setInterval(verificarEstado, 30000);
verificarEstado();

var savedScroll = sessionStorage.getItem('kanban_scroll');
if (savedScroll) { sessionStorage.removeItem('kanban_scroll'); window.scrollTo(parseInt(savedScroll), 0); }

var refreshDot = document.createElement('span');
refreshDot.style.cssText = 'width:8px;height:8px;background:#4ade80;border-radius:50%;display:inline-block;margin-left:8px;animation:pulse 2s infinite';
document.getElementById('totalCards').appendChild(refreshDot);
var sty = document.createElement('style');
sty.textContent = '@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}';
document.head.appendChild(sty);

// ── Mover card no DOM em tempo real ──────────────────────────
function moverCard(card, novoStatus) {
    const destLista = document.querySelector(`.kanban-lista[data-status="${novoStatus}"]`);
    if (!destLista) return;
    card.dataset.status = novoStatus;
    destLista.prepend(card);
    // Remove empty placeholder se existir
    const empty = destLista.querySelector('.empty-col');
    if (empty) empty.remove();
    updateCount();
}

// ── Atualizar botões do card após mudança de status ───────────
function atualizarBotoesCard(card, novoStatus) {
    // Remove botões de status específico
    card.querySelectorAll('.btn-iniciar, .btn-andamento, .btn-finalizar, .btn-arquivar').forEach(b => b.remove());
    const actions = card.querySelector('.card-actions');
    const id      = card.dataset.id;
    const valor   = card.dataset.valor;

    if (novoStatus === 'em_servico') {
        const btnAnd = document.createElement('button');
        btnAnd.className   = 'btn-andamento';
        btnAnd.dataset.id  = id;
        btnAnd.dataset.andamento = '';
        btnAnd.innerHTML   = '<i class="fas fa-pencil-alt"></i> Andamento';
        const btnFin = document.createElement('button');
        btnFin.className   = 'btn-finalizar';
        btnFin.dataset.id  = id;
        btnFin.dataset.valor = valor;
        btnFin.innerHTML   = '<i class="fas fa-check-circle"></i> Finalizar';
        actions.appendChild(btnAnd);
        actions.appendChild(btnFin);
    } else if (novoStatus === 'concluido') {
        const btnArq = document.createElement('button');
        btnArq.className   = 'btn-arquivar';
        btnArq.dataset.id  = id;
        btnArq.title       = 'Arquivar';
        btnArq.innerHTML   = '<i class="fas fa-archive"></i>';
        actions.appendChild(btnArq);
    }
}

// ── Botão INICIAR serviço ─────────────────────────────────────
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-iniciar');
    if (!btn) return;
    btn.disabled = true;
    var card = btn.closest('.kanban-card');
    var id   = btn.dataset.id;

    fetch(`/kanban/${id}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ status: 'em_servico' }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            moverCard(card, 'em_servico');
            atualizarBotoesCard(card, 'em_servico');
            hashAtual = null;
            toast('Serviço iniciado!');
        } else {
            toast(data.msg || 'Erro ao iniciar.', true);
            btn.disabled = false;
        }
    })
    .catch(() => { toast('Erro de conexão.', true); btn.disabled = false; });
});

// ── Botão ANDAMENTO ───────────────────────────────────────────
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-andamento');
    if (!btn) return;
    andamentoOrcId = btn.dataset.id;
    document.getElementById('textoAndamento').value = btn.dataset.andamento || '';
    $('#modalAndamento').modal('show');
});

document.getElementById('btnSalvarAndamento').addEventListener('click', function () {
    if (!andamentoOrcId) return;
    var texto = document.getElementById('textoAndamento').value;
    this.disabled = true;
    fetch(`/kanban/${andamentoOrcId}/andamento`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ andamento: texto }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            // Atualiza data-andamento no botão
            var card = document.querySelector(`.kanban-card[data-id="${andamentoOrcId}"]`);
            if (card) { var ab = card.querySelector('.btn-andamento'); if (ab) ab.dataset.andamento = texto; }
            $('#modalAndamento').modal('hide');
            toast('Andamento registrado!');
        } else { toast('Erro ao salvar.', true); }
    })
    .catch(() => toast('Erro de conexão.', true))
    .finally(() => { this.disabled = false; });
});

// ── Modal PAGAMENTO ───────────────────────────────────────────
function abrirModalPagamento(id, valor, card, colOrigem) {
    pagamentoOrcId   = id;
    pagamentoCardEl  = card;
    pagamentoOrigCol = colOrigem;
    document.getElementById('infoPagamento').textContent =
        'Serviço: ' + (card ? card.querySelector('.card-cliente').textContent : '') +
        ' | Total: R$ ' + parseFloat(valor).toFixed(2).replace('.', ',');
    document.getElementById('listaPagamentos').innerHTML = '';
    adicionarLinhaPagamento(parseFloat(valor).toFixed(2));
    atualizarTotalPagamentos();
    $('#modalPagamento').modal('show');
}

function adicionarLinhaPagamento(valor = '') {
    var row = document.createElement('div');
    row.className = 'pagamento-row';
    row.innerHTML = `
        <select class="form-control form-control-sm" style="background:#0f172a;color:#e2e8f0;border-color:#334155;max-width:160px;">
            <option value="PIX">PIX</option>
            <option value="Dinheiro">Dinheiro</option>
            <option value="Crédito">Cartão de Crédito</option>
            <option value="Débito">Cartão de Débito</option>
        </select>
        <input type="number" class="form-control form-control-sm" style="background:#0f172a;color:#e2e8f0;border-color:#334155;"
               placeholder="Valor" value="${valor}" min="0.01" step="0.01">
        <button type="button" class="btn-remove-pag" title="Remover">&times;</button>
    `;
    row.querySelector('.btn-remove-pag').addEventListener('click', function () {
        if (document.querySelectorAll('.pagamento-row').length > 1) {
            row.remove(); atualizarTotalPagamentos();
        }
    });
    row.querySelector('input').addEventListener('input', atualizarTotalPagamentos);
    document.getElementById('listaPagamentos').appendChild(row);
}

function atualizarTotalPagamentos() {
    var total = 0;
    document.querySelectorAll('#listaPagamentos input[type=number]').forEach(i => {
        total += parseFloat(i.value || 0);
    });
    document.getElementById('totalPagamentos').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
}

document.getElementById('btnAddPagamento').addEventListener('click', () => adicionarLinhaPagamento());

function cancelarPagamento() {
    // Devolve card para coluna original se foi arrastado
    if (pagamentoCardEl && pagamentoOrigCol) {
        pagamentoOrigCol.appendChild(pagamentoCardEl);
        updateCount();
    }
    pagamentoOrcId = pagamentoCardEl = pagamentoOrigCol = null;
    $('#modalPagamento').modal('hide');
}
document.getElementById('btnCancelarPagamento').addEventListener('click', cancelarPagamento);
document.getElementById('btnCancelarPagamento2').addEventListener('click', cancelarPagamento);

document.getElementById('btnConfirmarPagamento').addEventListener('click', function () {
    if (!pagamentoOrcId) return;
    var pagamentos = [];
    var valido = true;
    document.querySelectorAll('#listaPagamentos .pagamento-row').forEach(row => {
        var forma = row.querySelector('select').value;
        var valor = parseFloat(row.querySelector('input').value || 0);
        if (valor <= 0) { valido = false; return; }
        pagamentos.push({ forma, valor });
    });
    if (!valido || pagamentos.length === 0) { toast('Informe valores válidos para todos os pagamentos.', true); return; }

    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processando...';

    fetch(`/kanban/${pagamentoOrcId}/concluir`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ pagamentos }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            $('#modalPagamento').modal('hide');
            // Move card para coluna concluido
            if (pagamentoCardEl) {
                moverCard(pagamentoCardEl, 'concluido');
                atualizarBotoesCard(pagamentoCardEl, 'concluido');
            }
            hashAtual = null;
            // Mostra modal de sucesso
            document.getElementById('osNumero').textContent = 'OS: ' + data.numero_os;
            document.getElementById('osValor').textContent  = 'Serviço concluído com sucesso!';
            document.getElementById('btnImprimirOs').href   = data.pdf_url;
            if (data.wa_url) {
                document.getElementById('btnWaOs').href    = data.wa_url;
                document.getElementById('btnWaOs').style.display = '';
            } else {
                document.getElementById('btnWaOs').style.display = 'none';
            }
            pagamentoOrcId = pagamentoCardEl = pagamentoOrigCol = null;
            $('#modalOsGerada').modal('show');
        } else {
            toast(data.msg || 'Erro ao finalizar.', true);
        }
    })
    .catch(() => toast('Erro de conexão.', true))
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-check mr-1"></i> Confirmar e Gerar OS';
    });
});

// ── Botão FINALIZAR (em_servico) ──────────────────────────────
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-finalizar');
    if (!btn) return;
    var card  = btn.closest('.kanban-card');
    var id    = btn.dataset.id;
    var valor = btn.dataset.valor || card.dataset.valor;
    abrirModalPagamento(id, valor, card, null);
});

// ── Arquivar card concluído ───────────────────────────────────
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-arquivar');
    if (!btn) return;
    if (!confirm('Arquivar este serviço concluído? Ele sairá do Kanban.')) return;
    var id   = btn.dataset.id;
    var card = btn.closest('.kanban-card');
    btn.disabled = true;
    fetch('/kanban/' + id + '/arquivar', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            card.style.transition = 'opacity .3s, transform .3s';
            card.style.opacity    = '0';
            card.style.transform  = 'scale(.9)';
            setTimeout(() => { card.remove(); updateCount(); }, 300);
            toast('Arquivado com sucesso');
        } else { toast(data.msg || 'Erro ao arquivar.', true); btn.disabled = false; }
    })
    .catch(() => { toast('Erro de conexão.', true); btn.disabled = false; });
});

// ── Fechar modal OS ───────────────────────────────────────────
document.getElementById('btnFecharOs').addEventListener('click', function () {
    $('#modalOsGerada').modal('hide');
});

// ── SortableJS drag-and-drop ──────────────────────────────────
document.querySelectorAll('.kanban-lista').forEach(lista => {
    Sortable.create(lista, {
        group:      'kanban',
        animation:  180,
        ghostClass: 'sortable-ghost',
        chosenClass:'sortable-chosen',

        onEnd(evt) {
            const card        = evt.item;
            const orcId       = card.dataset.id;
            const novoStatus  = evt.to.dataset.status;
            const velhoStatus = evt.from.dataset.status;

            if (novoStatus === velhoStatus) return;

            // Drag para CONCLUÍDO → devolve card e abre modal de pagamento
            if (novoStatus === 'concluido') {
                evt.from.insertBefore(card, evt.from.children[evt.oldIndex] || null);
                updateCount();
                var valor = card.dataset.valor;
                abrirModalPagamento(orcId, valor, card, evt.from);
                return;
            }

            card.classList.add('saving');

            fetch(`/kanban/${orcId}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ status: novoStatus }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    card.dataset.status = novoStatus;
                    if (data.token) card.dataset.token = data.token;
                    atualizarBotoesCard(card, novoStatus);
                    hashAtual = null;
                    toast('Status: ' + novoStatus.replace('_', ' '));
                } else {
                    toast(data.msg || 'Erro ao salvar.', true);
                    evt.from.appendChild(card);
                }
            })
            .catch(() => { toast('Erro de conexão.', true); evt.from.appendChild(card); })
            .finally(() => { card.classList.remove('saving'); updateCount(); });
        },
    });
});
</script>
</body>
</html>
