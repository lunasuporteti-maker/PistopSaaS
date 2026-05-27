@extends('layouts.pitstop')
@section('title', $orcamento->exists ? 'Editar Orçamento' : 'Novo Orçamento')

@section('content_header')
    <h1>{{ $orcamento->exists ? 'Editar Orçamento #' . $orcamento->id : 'Novo Orçamento' }}</h1>
@endsection

@section('content')

<div class="card card-danger card-outline">
    <div class="card-body">
        <form method="POST" action="{{ $orcamento->exists ? route('orcamentos.update', $orcamento) : route('orcamentos.store') }}">
            @csrf
            @if($orcamento->exists) @method('PUT') @endif

            <div class="row">
                {{-- Cliente --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Cliente <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="cliente_id" id="clienteSelect"
                                    class="form-control @error('cliente_id') is-invalid @enderror" required>
                                <option value="">Selecione o cliente...</option>
                                @foreach($clientes as $c)
                                <option value="{{ $c->id }}"
                                    {{ old('cliente_id', $orcamento->cliente_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->nome }} {{ $c->telefone ? '— ' . $c->telefone : '' }}
                                </option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-danger"
                                        data-toggle="modal" data-target="#modalNovoCliente"
                                        title="Cadastrar novo cliente">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            </div>
                        </div>
                        @error('cliente_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Veículo --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Veículo <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="veiculo_id" id="veiculoSelect"
                                    class="form-control @error('veiculo_id') is-invalid @enderror" required>
                                <option value="">Selecione o cliente primeiro...</option>
                                @if($orcamento->exists && $orcamento->veiculo)
                                <option value="{{ $orcamento->veiculo_id }}" selected>
                                    {{ $orcamento->veiculo->marca }} {{ $orcamento->veiculo->modelo }}
                                    — {{ $orcamento->veiculo->placa ?? 'S/placa' }}
                                </option>
                                @endif
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-danger" id="btnNovoVeiculo"
                                        data-toggle="modal" data-target="#modalNovoVeiculo"
                                        title="Cadastrar novo veículo" disabled>
                                    <i class="fas fa-car"></i> <i class="fas fa-plus" style="font-size:.7rem"></i>
                                </button>
                            </div>
                        </div>
                        @error('veiculo_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>KM Entrada</label>
                        <input type="number" name="km_entrada" class="form-control"
                               value="{{ old('km_entrada', $orcamento->km_entrada) }}" placeholder="0">
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="form-group">
                        <label>Queixa do Cliente</label>
                        <input type="text" name="queixa_cliente" class="form-control"
                               value="{{ old('queixa_cliente', $orcamento->queixa_cliente) }}"
                               placeholder="Descreva o problema relatado...">
                    </div>
                </div>
                @if($orcamento->exists)
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Parecer Técnico</label>
                        <textarea name="parecer_tecnico" class="form-control" rows="2">{{ old('parecer_tecnico', $orcamento->parecer_tecnico) }}</textarea>
                    </div>
                </div>
                @endif
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea name="observacao" class="form-control" rows="2">{{ old('observacao', $orcamento->observacao) }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-danger">
                <i class="fas fa-save"></i> Salvar Orçamento
            </button>
            <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </form>
    </div>
</div>

{{-- ─── Modal: Novo Cliente ─────────────────────────────── --}}
<div class="modal fade" id="modalNovoCliente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus mr-1"></i> Novo Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="clienteErro" class="alert alert-danger d-none"></div>
                <div class="form-group">
                    <label>Nome <span class="text-danger">*</span></label>
                    <input type="text" id="c_nome" class="form-control" placeholder="Nome completo" required>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" id="c_telefone" class="form-control" placeholder="(99) 99999-9999">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>CPF</label>
                            <input type="text" id="c_cpf" class="form-control" placeholder="000.000.000-00">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnSalvarCliente">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ─── Modal: Novo Veículo ─────────────────────────────── --}}
<div class="modal fade" id="modalNovoVeiculo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-car mr-1"></i> Novo Veículo</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="veiculoErro" class="alert alert-danger d-none"></div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Marca</label>
                            <input type="text" id="v_marca" class="form-control" placeholder="Honda, Toyota...">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Modelo</label>
                            <input type="text" id="v_modelo" class="form-control" placeholder="Civic, Corolla...">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Placa</label>
                            <input type="text" id="v_placa" class="form-control" placeholder="ABC-1234"
                                   style="text-transform:uppercase">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Ano</label>
                            <input type="number" id="v_ano" class="form-control" placeholder="{{ date('Y') }}">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Cor</label>
                            <input type="text" id="v_cor" class="form-control" placeholder="Branco">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnSalvarVeiculo">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
const csrfToken = '{{ csrf_token() }}';

// ── Carrega veículos ao trocar cliente ───────────────────────
function carregarVeiculos(clienteId, selecionarId = null) {
    const sel = document.getElementById('veiculoSelect');
    const btnVeiculo = document.getElementById('btnNovoVeiculo');

    if (!clienteId) {
        sel.innerHTML = '<option value="">Selecione o cliente primeiro...</option>';
        btnVeiculo.disabled = true;
        return;
    }

    sel.innerHTML = '<option value="">Carregando...</option>';
    btnVeiculo.disabled = false;

    fetch(`/json/veiculos-por-cliente/${clienteId}`)
        .then(r => r.json())
        .then(veiculos => {
            sel.innerHTML = '<option value="">Selecione o veículo...</option>';
            veiculos.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = `${v.marca || ''} ${v.modelo || ''} — ${v.placa || 'S/placa'}`.trim();
                if (selecionarId && v.id == selecionarId) opt.selected = true;
                sel.appendChild(opt);
            });
            if (veiculos.length === 0) {
                sel.innerHTML = '<option value="">Nenhum veículo cadastrado</option>';
            }
        });
}

document.getElementById('clienteSelect').addEventListener('change', function () {
    carregarVeiculos(this.value);
});

// Ao carregar a página, se cliente já estiver selecionado, carrega veículos
const clienteInicial = document.getElementById('clienteSelect').value;
const veiculoInicial  = '{{ $orcamento->veiculo_id ?? "" }}';
if (clienteInicial) carregarVeiculos(clienteInicial, veiculoInicial);

// ── Salvar novo cliente via modal ────────────────────────────
document.getElementById('btnSalvarCliente').addEventListener('click', function () {
    const nome     = document.getElementById('c_nome').value.trim();
    const telefone = document.getElementById('c_telefone').value.trim();
    const cpf      = document.getElementById('c_cpf').value.trim();
    const erroDiv  = document.getElementById('clienteErro');

    if (!nome) { erroDiv.textContent = 'Nome é obrigatório.'; erroDiv.classList.remove('d-none'); return; }
    erroDiv.classList.add('d-none');
    this.disabled = true;

    fetch('{{ route("json.clientes.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ nome, telefone, cpf })
    })
    .then(r => r.json())
    .then(cliente => {
        if (cliente.errors) {
            erroDiv.textContent = Object.values(cliente.errors).flat().join(', ');
            erroDiv.classList.remove('d-none');
            return;
        }
        // Adiciona o novo cliente no select e o seleciona
        const sel = document.getElementById('clienteSelect');
        const opt = document.createElement('option');
        opt.value = cliente.id;
        opt.textContent = `${cliente.nome}${cliente.telefone ? ' — ' + cliente.telefone : ''}`;
        opt.selected = true;
        sel.appendChild(opt);
        sel.dispatchEvent(new Event('change'));

        // Limpa campos e fecha modal
        ['c_nome','c_telefone','c_cpf'].forEach(id => document.getElementById(id).value = '');
        $('#modalNovoCliente').modal('hide');
    })
    .catch(() => { erroDiv.textContent = 'Erro ao salvar cliente.'; erroDiv.classList.remove('d-none'); })
    .finally(() => this.disabled = false);
});

// ── Salvar novo veículo via modal ────────────────────────────
document.getElementById('btnSalvarVeiculo').addEventListener('click', function () {
    const clienteId = document.getElementById('clienteSelect').value;
    const erroDiv   = document.getElementById('veiculoErro');

    if (!clienteId) { erroDiv.textContent = 'Selecione um cliente antes.'; erroDiv.classList.remove('d-none'); return; }
    erroDiv.classList.add('d-none');
    this.disabled = true;

    const payload = {
        cliente_id: clienteId,
        marca:  document.getElementById('v_marca').value.trim(),
        modelo: document.getElementById('v_modelo').value.trim(),
        placa:  document.getElementById('v_placa').value.trim().toUpperCase(),
        ano:    document.getElementById('v_ano').value   || null,
        cor:    document.getElementById('v_cor').value.trim() || null,
    };

    fetch('{{ route("json.veiculos.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(veiculo => {
        if (veiculo.errors) {
            erroDiv.textContent = Object.values(veiculo.errors).flat().join(', ');
            erroDiv.classList.remove('d-none');
            return;
        }
        // Adiciona no select de veículos e seleciona
        const sel = document.getElementById('veiculoSelect');
        const opt = document.createElement('option');
        opt.value = veiculo.id;
        opt.textContent = `${veiculo.marca || ''} ${veiculo.modelo || ''} — ${veiculo.placa || 'S/placa'}`.trim();
        opt.selected = true;
        sel.appendChild(opt);

        ['v_marca','v_modelo','v_placa','v_ano','v_cor'].forEach(id => document.getElementById(id).value = '');
        $('#modalNovoVeiculo').modal('hide');
    })
    .catch(() => { erroDiv.textContent = 'Erro ao salvar veículo.'; erroDiv.classList.remove('d-none'); })
    .finally(() => this.disabled = false);
});
</script>
@endpush
