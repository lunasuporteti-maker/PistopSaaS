@extends('layouts.pitstop')
@section('title', $agendamento->exists ? 'Editar Agendamento' : 'Novo Agendamento')

@section('content_header')
    <h1>{{ $agendamento->exists ? 'Editar Agendamento' : 'Novo Agendamento' }}</h1>
@endsection

@section('content')

<div class="card card-danger card-outline" style="max-width:700px">
    <div class="card-body">
        <form method="POST" action="{{ $agendamento->exists ? route('agendamentos.update', $agendamento) : route('agendamentos.store') }}">
            @csrf
            @if($agendamento->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Cliente <span class="text-danger">*</span></label>
                        <select name="cliente_id" id="clienteSelect" class="form-control" required>
                            <option value="">Selecione...</option>
                            @foreach($clientes as $c)
                            <option value="{{ $c->id }}" {{ old('cliente_id', $agendamento->cliente_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Veículo</label>
                        <select name="veiculo_id" id="veiculoSelect" class="form-control">
                            <option value="">Selecione...</option>
                            @if($agendamento->exists && $agendamento->veiculo)
                            <option value="{{ $agendamento->veiculo_id }}" selected>
                                {{ $agendamento->veiculo->marca }} {{ $agendamento->veiculo->modelo }}
                            </option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Data e Hora <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="data_hora" class="form-control" required
                               value="{{ old('data_hora', $agendamento->data_hora?->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Serviço</label>
                        <input type="text" name="servico" class="form-control" value="{{ old('servico', $agendamento->servico) }}" placeholder="Ex: Troca de óleo, Alinhamento...">
                    </div>
                </div>
                @if($agendamento->exists)
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            @foreach(['agendado','confirmado','realizado','cancelado'] as $s)
                            <option value="{{ $s }}" {{ $agendamento->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Resultado</label>
                        <input type="text" name="resultado" class="form-control" value="{{ old('resultado', $agendamento->resultado) }}">
                    </div>
                </div>
                @endif
                <div class="col-12">
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea name="observacao" class="form-control" rows="2">{{ old('observacao', $agendamento->observacao) }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Salvar</button>
            <a href="{{ route('agendamentos.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
document.getElementById('clienteSelect').addEventListener('change', function() {
    const id = this.value;
    const sel = document.getElementById('veiculoSelect');
    if (!id) { sel.innerHTML = '<option value="">Selecione...</option>'; return; }
    sel.innerHTML = '<option value="">Carregando...</option>';
    fetch(`/json/veiculos-por-cliente/${id}`)
        .then(r=>r.json()).then(vs=>{
            sel.innerHTML = '<option value="">Selecione...</option>';
            vs.forEach(v => sel.innerHTML += `<option value="${v.id}">${v.marca||''} ${v.modelo||''} — ${v.placa||'S/placa'}</option>`);
            if (!vs.length) sel.innerHTML = '<option value="">Nenhum veículo cadastrado</option>';
        });
});
</script>
@endpush
