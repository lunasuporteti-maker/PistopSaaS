@extends('adminlte::page')
@section('title', 'Lembretes')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1 class="m-0"><i class="fas fa-bell mr-2 text-danger"></i>Lembretes</h1>
        <small class="text-muted">{{ $contadores['pendente'] }} pendente(s) · {{ $contadores['concluido'] }} concluído(s)</small>
    </div>
    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalNovoLembrete">
        <i class="fas fa-plus mr-1"></i> Novo Lembrete
    </button>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

{{-- Filtros --}}
<div class="d-flex mb-3" style="gap:6px">
    @foreach(['pendente'=>['warning','Pendentes'],'concluido'=>['success','Concluídos'],'todos'=>['secondary','Todos']] as $s => $cfg)
    <a href="{{ route('lembretes.index', ['status' => $s]) }}"
       class="btn btn-sm btn-{{ $filtroStatus === $s ? $cfg[0] : 'outline-' . $cfg[0] }}">
        {{ $cfg[1] }}
        @if($s !== 'todos' && isset($contadores[$s]))
            <span class="badge badge-light ml-1">{{ $contadores[$s] }}</span>
        @endif
    </a>
    @endforeach
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Título / Assunto</th>
                    <th>Vinculado a</th>
                    <th>Data / Hora</th>
                    <th>Status</th>
                    <th class="text-right pr-3">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lembretes as $l)
                @php
                    $vencido = $l->data_lembrete->isPast() && $l->status === 'pendente';
                    $hoje    = $l->data_lembrete->isToday() && $l->status === 'pendente';
                @endphp
                <tr class="{{ $vencido ? 'table-warning' : ($l->status === 'concluido' ? 'table-success' : '') }}">
                    <td>
                        <strong {{ $l->status === 'concluido' ? 'style=text-decoration:line-through;color:#888' : '' }}>
                            {{ $l->titulo ?? $l->servico_nome }}
                        </strong>
                        @if($l->observacao)
                        <br><small class="text-muted">{{ Str::limit($l->observacao, 70) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($l->cliente)
                            <i class="fas fa-user mr-1 text-muted"></i>{{ $l->cliente->nome }}
                            @if($l->veiculo)
                            <br><small class="text-muted"><i class="fas fa-car mr-1"></i>{{ $l->veiculo->marca }} {{ $l->veiculo->modelo }}</small>
                            @endif
                        @else
                            <span class="text-muted small">— Geral —</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $l->data_lembrete->format('d/m/Y') }}</strong>
                        @if($l->hora_lembrete)
                        <br><small class="text-muted"><i class="fas fa-clock mr-1"></i>{{ substr($l->hora_lembrete, 0, 5) }}</small>
                        @endif
                        @if($vencido)
                            <span class="badge badge-warning d-block mt-1" style="width:fit-content">Vencido</span>
                        @elseif($hoje)
                            <span class="badge badge-danger d-block mt-1" style="width:fit-content">Hoje!</span>
                        @endif
                    </td>
                    <td>
                        @php $badgeStatus = ['pendente'=>'warning','concluido'=>'success','cancelado'=>'secondary'] @endphp
                        <span class="badge badge-{{ $badgeStatus[$l->status] ?? 'secondary' }}">
                            {{ ucfirst($l->status) }}
                        </span>
                    </td>
                    <td class="text-right pr-3">
                        <div class="d-flex justify-content-end align-items-center" style="gap:4px">
                            {{-- WhatsApp --}}
                            @if($l->cliente && $l->cliente->telefone)
                            @php
                                $titulo = $l->titulo ?? $l->servico_nome;
                                $hora   = $l->hora_lembrete ? ' às ' . substr($l->hora_lembrete, 0, 5) : '';
                                $waTxt  = urlencode("Olá {$l->cliente->nome}! Passando para lembrá-lo(a) sobre: *{$titulo}* — previsto para {$l->data_lembrete->format('d/m/Y')}{$hora}. Qualquer dúvida, entre em contato com a AutoFix.");
                                $waUrl  = 'https://wa.me/55' . preg_replace('/\D/','',$l->cliente->telefone) . '?text=' . $waTxt;
                            @endphp
                            <a href="{{ $waUrl }}" target="_blank" class="btn btn-xs btn-success" title="Enviar WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            @endif

                            {{-- Editar --}}
                            <button class="btn btn-xs btn-outline-primary btn-editar-lembrete"
                                    data-id="{{ $l->id }}"
                                    data-toggle="modal" data-target="#modalEditarLembrete"
                                    title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>

                            {{-- Concluir / Reabrir --}}
                            @if($l->status === 'pendente')
                            <form method="POST" action="{{ route('lembretes.update', $l) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="concluido">
                                <button class="btn btn-xs btn-success" title="Marcar como concluído">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('lembretes.update', $l) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="pendente">
                                <button class="btn btn-xs btn-outline-warning" title="Reabrir">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                            @endif

                            {{-- Excluir --}}
                            <form method="POST" action="{{ route('lembretes.destroy', $l) }}" class="d-inline"
                                  onsubmit="return confirm('Excluir este lembrete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="fas fa-bell-slash fa-2x mb-2 d-block text-light"></i>
                        Nenhum lembrete {{ $filtroStatus !== 'todos' ? $filtroStatus : '' }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($lembretes->hasPages())
    <div class="card-footer">{{ $lembretes->links() }}</div>
    @endif
</div>

{{-- Modal: Novo Lembrete --}}
<div class="modal fade" id="modalNovoLembrete" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-bell mr-2"></i>Novo Lembrete</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="{{ route('lembretes.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-600">Título / Assunto <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" class="form-control"
                               placeholder="EX: LIGAR PARA CLIENTE, COMPRAR PEÇAS..."
                               maxlength="200" data-uppercase required autofocus>
                    </div>
                    <div class="row">
                        <div class="col-7">
                            <div class="form-group">
                                <label class="font-weight-600">Data <span class="text-danger">*</span></label>
                                <input type="date" name="data_lembrete" class="form-control"
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="form-group">
                                <label class="font-weight-600">Hora <small class="text-muted">(opcional)</small></label>
                                <input type="time" name="hora_lembrete" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-600">Observação</label>
                        <textarea name="observacao" class="form-control" rows="2"
                                  placeholder="Detalhes adicionais..." maxlength="500"></textarea>
                    </div>
                    <hr>
                    <p class="text-muted small mb-2"><i class="fas fa-link mr-1"></i>Vincular a um cliente (opcional)</p>
                    <div class="form-group">
                        <label>Cliente</label>
                        <select name="cliente_id" id="novo-lembrete-cliente" class="form-control">
                            <option value="">— Sem cliente —</option>
                            @foreach($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="grp-veiculo-novo" style="display:none">
                        <label>Veículo</label>
                        <select name="veiculo_id" id="novo-lembrete-veiculo" class="form-control">
                            <option value="">— Sem veículo —</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-save mr-1"></i>Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Editar Lembrete --}}
<div class="modal fade" id="modalEditarLembrete" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Lembrete</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" id="form-editar-lembrete" action="">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-600">Título / Assunto <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="edit-titulo" class="form-control"
                               maxlength="200" data-uppercase required>
                    </div>
                    <div class="row">
                        <div class="col-7">
                            <div class="form-group">
                                <label class="font-weight-600">Data <span class="text-danger">*</span></label>
                                <input type="date" name="data_lembrete" id="edit-data" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="form-group">
                                <label class="font-weight-600">Hora <small class="text-muted">(opcional)</small></label>
                                <input type="time" name="hora_lembrete" id="edit-hora" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-600">Observação</label>
                        <textarea name="observacao" id="edit-observacao" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label>Cliente <small class="text-muted">(opcional)</small></label>
                        <select name="cliente_id" id="edit-cliente" class="form-control">
                            <option value="">— Sem cliente —</option>
                            @foreach($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="grp-veiculo-edit" style="display:none">
                        <label>Veículo</label>
                        <select name="veiculo_id" id="edit-veiculo" class="form-control">
                            <option value="">— Sem veículo —</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
// ── Veículos por cliente (modal novo) ─────────────────────────
document.getElementById('novo-lembrete-cliente').addEventListener('change', function () {
    carregarVeiculosLembrete(this.value, 'novo-lembrete-veiculo', 'grp-veiculo-novo');
});

// ── Botão editar: carrega dados do lembrete via AJAX ─────────
document.querySelectorAll('.btn-editar-lembrete').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var id = this.dataset.id;
        fetch('/lembretes/' + id + '/edit', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(function (l) {
                document.getElementById('edit-titulo').value      = l.titulo || '';
                document.getElementById('edit-data').value        = l.data_lembrete || '';
                document.getElementById('edit-hora').value        = l.hora_lembrete ? l.hora_lembrete.substring(0,5) : '';
                document.getElementById('edit-observacao').value  = l.observacao || '';
                document.getElementById('form-editar-lembrete').action = '/lembretes/' + l.id;

                // Seta cliente
                var selCliente = document.getElementById('edit-cliente');
                selCliente.value = l.cliente_id || '';
                if (l.cliente_id) {
                    document.getElementById('grp-veiculo-edit').style.display = '';
                    carregarVeiculosLembrete(l.cliente_id, 'edit-veiculo', 'grp-veiculo-edit', l.veiculo_id);
                } else {
                    document.getElementById('grp-veiculo-edit').style.display = 'none';
                }
            });
    });
});

document.getElementById('edit-cliente').addEventListener('change', function () {
    carregarVeiculosLembrete(this.value, 'edit-veiculo', 'grp-veiculo-edit');
});

function carregarVeiculosLembrete(clienteId, selId, grpId, selecionarId) {
    var sel = document.getElementById(selId);
    var grp = document.getElementById(grpId);
    if (!clienteId) { grp.style.display = 'none'; return; }
    grp.style.display = '';
    fetch('/json/veiculos-por-cliente/' + clienteId)
        .then(r => r.json())
        .then(function (veiculos) {
            sel.innerHTML = '<option value="">— Sem veículo —</option>';
            veiculos.forEach(function (v) {
                var opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.marca + ' ' + v.modelo + (v.placa ? ' — ' + v.placa : '');
                if (selecionarId && v.id == selecionarId) opt.selected = true;
                sel.appendChild(opt);
            });
        });
}

$('#modalNovoLembrete, #modalEditarLembrete').on('shown.bs.modal', function () {
    if (window.pitStopAplicarMascaras) pitStopAplicarMascaras();
});
</script>
@endpush
