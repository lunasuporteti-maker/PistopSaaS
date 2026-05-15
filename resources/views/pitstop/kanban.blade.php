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
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.4);
        }
        .kanban-header h1 { color: #fff; font-size: 1.4rem; margin: 0; font-weight: 700; letter-spacing: 1px; }
        .kanban-header .badge-count { background: rgba(255,255,255,0.2); color: #fff; padding: 3px 10px; border-radius: 12px; font-size: .85rem; }

        .kanban-board { display: flex; gap: 14px; padding: 16px; overflow-x: auto; height: calc(100vh - 58px); }

        .kanban-col {
            min-width: 280px;
            max-width: 300px;
            flex-shrink: 0;
            background: #16213e;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            max-height: 100%;
        }
        .col-header {
            padding: 12px 14px 10px;
            border-radius: 12px 12px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .col-header h3 { color: #fff; font-size: .95rem; font-weight: 700; margin: 0; }
        .col-count { background: rgba(255,255,255,0.25); color: #fff; font-size: .8rem; padding: 2px 8px; border-radius: 10px; font-weight: 700; }

        .col-body {
            padding: 10px;
            overflow-y: auto;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .col-body::-webkit-scrollbar { width: 4px; }
        .col-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }

        .kanban-card {
            background: #0f3460;
            border-radius: 10px;
            padding: 12px;
            cursor: grab;
            border-left: 4px solid transparent;
            transition: transform .15s, box-shadow .15s, opacity .15s;
            position: relative;
        }
        .kanban-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.4); }
        .kanban-card.sortable-ghost { opacity: 0.35; transform: scale(.97); }
        .kanban-card.sortable-chosen { cursor: grabbing; box-shadow: 0 10px 30px rgba(0,0,0,0.6); }
        .kanban-card.saving { opacity: 0.6; }

        .card-cliente { color: #e2e8f0; font-weight: 700; font-size: .9rem; margin-bottom: 3px; }
        .card-veiculo { color: #94a3b8; font-size: .8rem; margin-bottom: 6px; }
        .card-veiculo i { margin-right: 4px; }
        .card-valor { color: #4ade80; font-size: .85rem; font-weight: 600; }
        .card-data { color: #64748b; font-size: .75rem; }
        .card-queixa { color: #94a3b8; font-size: .75rem; margin-top: 6px; font-style: italic;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }

        .card-actions { display: flex; gap: 6px; margin-top: 10px; }
        .btn-wa {
            background: #25d366; color: #fff; border: none; border-radius: 6px;
            padding: 4px 10px; font-size: .78rem; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; gap: 5px; text-decoration: none;
            transition: background .2s;
        }
        .btn-wa:hover { background: #1da851; color: #fff; text-decoration: none; }
        .btn-ver {
            background: rgba(255,255,255,0.1); color: #94a3b8; border: none; border-radius: 6px;
            padding: 4px 10px; font-size: .78rem; cursor: pointer; text-decoration: none;
            transition: background .2s;
        }
        .btn-ver:hover { background: rgba(255,255,255,0.2); color: #fff; text-decoration: none; }

        /* Toast de feedback */
        #toast {
            position: fixed; bottom: 20px; right: 20px; z-index: 9999;
            background: #1e293b; color: #e2e8f0; padding: 10px 18px;
            border-radius: 8px; font-size: .85rem; border-left: 4px solid #4ade80;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5); opacity: 0; transition: opacity .3s;
            pointer-events: none;
        }
        #toast.show { opacity: 1; }
        #toast.erro { border-color: #f87171; }

        .empty-col { color: #334155; text-align: center; padding: 20px 10px; font-size: .85rem; }

        @media (max-width: 600px) { .kanban-col { min-width: 240px; } }
    </style>
</head>
<body>

<div class="kanban-header">
    <h1><i class="fas fa-columns mr-2"></i>PitStop — Painel Kanban</h1>
    <div class="d-flex align-items-center gap-2">
        <span class="badge-count" id="totalCards">
            {{ $cards->flatten()->count() }} cards
        </span>
        <button onclick="window.close()" class="btn btn-sm btn-outline-light ml-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<div class="kanban-board" id="kanbanBoard">
    @foreach($colunas as $status => $coluna)
    @php
        $itens = $cards->get($status, collect());
    @endphp
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
                $msg         = str_replace(
                    ['{nome}', '{veiculo}'],
                    [$nomeCliente, $nomeVeiculo ?: 'veículo'],
                    $mensagens[$status]
                );
                $waUrl = $telefone
                    ? 'https://wa.me/55' . $telefone . '?text=' . urlencode($msg)
                    : null;
            @endphp
            <div class="kanban-card"
                 data-id="{{ $orc->id }}"
                 data-status="{{ $status }}"
                 draggable="true">
                <div class="card-cliente">{{ $nomeCliente }}</div>
                <div class="card-veiculo">
                    <i class="fas fa-car"></i>
                    {{ $nomeVeiculo ?: '—' }}
                    @if($orc->veiculo->placa ?? null)
                        <span style="color:#475569"> · {{ $orc->veiculo->placa }}</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="card-valor">
                        R$ {{ number_format($orc->valor_total, 2, ',', '.') }}
                    </span>
                    <span class="card-data">{{ $orc->created_at->format('d/m') }}</span>
                </div>
                @if($orc->queixa_cliente)
                <div class="card-queixa" title="{{ $orc->queixa_cliente }}">
                    "{{ $orc->queixa_cliente }}"
                </div>
                @endif
                <div class="card-actions">
                    @if($waUrl)
                    <a href="{{ $waUrl }}" target="_blank" class="btn-wa">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    @endif
                    <a href="/orcamentos/{{ $orc->id }}" target="_blank" class="btn-ver">
                        <i class="fas fa-eye"></i> Ver
                    </a>
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

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function toast(msg, erro = false) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className   = 'show' + (erro ? ' erro' : '');
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => el.className = '', 2500);
}

function updateCount() {
    let total = 0;
    document.querySelectorAll('.kanban-lista').forEach(lista => {
        const cards = lista.querySelectorAll('.kanban-card').length;
        total += cards;
        const col   = lista.closest('.kanban-col');
        const count = col.querySelector('.col-count');
        if (count) count.textContent = cards;
    });
    document.getElementById('totalCards').textContent = total + ' cards';
}

// Inicializar SortableJS em cada coluna
document.querySelectorAll('.kanban-lista').forEach(lista => {
    Sortable.create(lista, {
        group:     'kanban',
        animation: 180,
        ghostClass:'sortable-ghost',
        chosenClass:'sortable-chosen',

        onEnd(evt) {
            const card      = evt.item;
            const orcId     = card.dataset.id;
            const novoStatus= evt.to.dataset.status;
            const velhoStatus= evt.from.dataset.status;

            if (novoStatus === velhoStatus) return; // mesma coluna, não salva

            card.classList.add('saving');

            fetch(`/kanban/${orcId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type':  'application/json',
                    'X-CSRF-TOKEN':  csrfToken,
                    'Accept':        'application/json',
                },
                body: JSON.stringify({ status: novoStatus }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    card.dataset.status = novoStatus;
                    toast('✓ Status atualizado para "' + novoStatus.replace('_', ' ') + '"');
                } else {
                    toast('Erro ao salvar.', true);
                    // Reverter: move o card de volta
                    evt.from.appendChild(card);
                }
            })
            .catch(() => {
                toast('Erro de conexão.', true);
                evt.from.appendChild(card);
            })
            .finally(() => {
                card.classList.remove('saving');
                updateCount();
            });
        },
    });
});
</script>
</body>
</html>
