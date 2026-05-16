@extends('adminlte::page')
@section('title', 'Lembretes')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0"><i class="fas fa-bell mr-1 text-danger"></i> Lembretes</h1>
    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalNovoLembrete">
        <i class="fas fa-plus mr-1"></i> Novo Lembrete
    </button>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Cliente</th>
                    <th>Veículo</th>
                    <th>Serviço / Motivo</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lembretes as $l)
                <tr class="{{ $l->data_lembrete->isPast() ? 'table-warning' : '' }}">
                    <td><strong>{{ $l->cliente->nome }}</strong></td>
                    <td>{{ $l->veiculo ? $l->veiculo->marca . ' ' . $l->veiculo->modelo : '—' }}</td>
                    <td>{{ $l->servico_nome }}</td>
                    <td>
                        {{ $l->data_lembrete->format('d/m/Y') }}
                        @if($l->data_lembrete->isPast())
                            <span class="badge badge-warning ml-1">Vencido</span>
                        @elseif($l->data_lembrete->isToday())
                            <span class="badge badge-danger ml-1">Hoje!</span>
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $l->status === 'enviado' ? 'success' : 'secondary' }}">{{ ucfirst($l->status) }}</span></td>
                    <td class="text-right">
                        @if($l->cliente->telefone)
                        @php
                            $waMsgLembrete = urlencode("Olá {$l->cliente->nome}! 👋 Passando para lembrá-lo(a) sobre: *{$l->servico_nome}*. Entre em contato com a AutoFix para agendar. 😊");
                            $waLembrete = 'https://wa.me/55' . preg_replace('/\D/', '', $l->cliente->telefone) . '?text=' . $waMsgLembrete;
                        @endphp
                        <a href="{{ $waLembrete }}" target="_blank" class="btn btn-sm btn-success" title="Enviar WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        @endif
                        <form method="POST" action="{{ route('lembretes.update', $l) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="enviado">
                            <button class="btn btn-sm btn-outline-success" title="Marcar como enviado">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('lembretes.destroy', $l) }}" class="d-inline"
                              onsubmit="return confirm('Excluir este lembrete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">
                    <i class="fas fa-check-circle text-success mr-1"></i> Nenhum lembrete pendente.
                </td></tr>
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
                        <label>Cliente <span class="text-danger">*</span></label>
                        <select name="cliente_id" id="lembrete-cliente" class="form-control" required>
                            <option value="">Selecione o cliente...</option>
                            @foreach($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Veículo</label>
                        <select name="veiculo_id" id="lembrete-veiculo" class="form-control">
                            <option value="">Selecione o cliente primeiro...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Serviço / Motivo <span class="text-danger">*</span></label>
                        <input type="text" name="servico_nome" class="form-control"
                               placeholder="Ex: REVISÃO DE 10.000 KM, TROCA DE PNEUS..."
                               maxlength="200" data-uppercase required>
                    </div>
                    <div class="form-group">
                        <label>Data do Lembrete <span class="text-danger">*</span></label>
                        <input type="date" name="data_lembrete" class="form-control"
                               min="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save mr-1"></i> Salvar Lembrete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.getElementById('lembrete-cliente').addEventListener('change', function () {
    var clienteId = this.value;
    var sel = document.getElementById('lembrete-veiculo');
    if (!clienteId) {
        sel.innerHTML = '<option value="">Selecione o cliente primeiro...</option>';
        return;
    }
    fetch('/json/veiculos-por-cliente/' + clienteId)
        .then(r => r.json())
        .then(veiculos => {
            sel.innerHTML = '<option value="">Sem veículo específico</option>';
            veiculos.forEach(v => {
                var opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.marca + ' ' + v.modelo + (v.placa ? ' — ' + v.placa : '');
                sel.appendChild(opt);
            });
        });
});
</script>
@endpush
