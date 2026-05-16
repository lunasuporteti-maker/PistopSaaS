@extends('adminlte::page')
@section('title', $cliente->exists ? 'Editar Cliente' : 'Novo Cliente')

@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('clientes.index') }}" class="btn btn-sm btn-outline-secondary mr-3"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="m-0 font-weight-bold text-dark">
            <i class="fas fa-user-plus mr-2 text-danger"></i>
            {{ $cliente->exists ? 'Editar Cliente' : 'Novo Cliente' }}
        </h1>
    </div>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="card card-outline card-danger shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}">
            @csrf
            @if($cliente->exists) @method('PUT') @endif

            {{-- Dados pessoais --}}
            <h6 class="text-danger font-weight-bold mb-3"><i class="fas fa-user mr-1"></i> DADOS PESSOAIS</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" name="nome"
                               class="form-control @error('nome') is-invalid @enderror"
                               value="{{ old('nome', $cliente->nome) }}"
                               placeholder="NOME DO CLIENTE"
                               maxlength="120" data-uppercase data-no-special required autocomplete="off">
                        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Telefone / WhatsApp</label>
                        <input type="text" name="telefone"
                               class="form-control"
                               value="{{ old('telefone', $cliente->telefone) }}"
                               placeholder="(99) 99999-9999" maxlength="20" data-phone>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" name="cpf"
                               class="form-control @error('cpf') is-invalid @enderror"
                               value="{{ old('cpf', $cliente->cpf) }}"
                               placeholder="000.000.000-00" maxlength="14" data-cpf>
                        @error('cpf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $cliente->email) }}"
                               placeholder="cliente@email.com" maxlength="120">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <hr>
            {{-- Endereço com busca por CEP --}}
            <h6 class="text-danger font-weight-bold mb-3"><i class="fas fa-map-marker-alt mr-1"></i> ENDEREÇO</h6>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>CEP</label>
                        <div class="input-group">
                            <input type="text" id="cep" name="cep"
                                   class="form-control"
                                   value="{{ old('cep', $cliente->cep) }}"
                                   placeholder="00000-000" maxlength="9" data-cep-input>
                            <div class="input-group-append">
                                <span class="input-group-text" id="cep-spinner" style="display:none">
                                    <i class="fas fa-spinner fa-spin text-muted"></i>
                                </span>
                            </div>
                        </div>
                        <small class="text-muted">Digite para preencher</small>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Logradouro (Rua/Av.)</label>
                        <input type="text" id="logradouro" name="logradouro"
                               class="form-control"
                               value="{{ old('logradouro', $cliente->logradouro) }}"
                               placeholder="Rua / Avenida" maxlength="150" data-uppercase>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Número</label>
                        <input type="text" id="numero" name="numero"
                               class="form-control"
                               value="{{ old('numero', $cliente->numero) }}"
                               placeholder="123" maxlength="20">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Bairro</label>
                        <input type="text" id="bairro" name="bairro"
                               class="form-control"
                               value="{{ old('bairro', $cliente->bairro) }}"
                               placeholder="Bairro" maxlength="80" data-uppercase>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Cidade</label>
                        <input type="text" id="cidade" name="cidade"
                               class="form-control"
                               value="{{ old('cidade', $cliente->cidade) }}"
                               placeholder="Cidade" maxlength="80" data-uppercase>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>UF</label>
                        <input type="text" id="uf" name="uf"
                               class="form-control"
                               value="{{ old('uf', $cliente->uf) }}"
                               placeholder="RN" maxlength="2" data-uppercase>
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-save mr-1"></i> Salvar Cliente
                </button>
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary ml-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
(function () {
    var cepInput = document.querySelector('[data-cep-input]');
    if (!cepInput) return;

    cepInput.addEventListener('input', function () {
        // Máscara CEP
        var digits = this.value.replace(/\D/g, '').substring(0, 8);
        this.value = digits.length > 5 ? digits.substring(0,5) + '-' + digits.substring(5) : digits;

        if (digits.length === 8) buscarCep(digits);
    });

    function buscarCep(cep) {
        var spinner = document.getElementById('cep-spinner');
        if (spinner) spinner.style.display = '';

        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (spinner) spinner.style.display = 'none';
                if (d.erro) return;

                setValue('logradouro', (d.logradouro || '').toUpperCase());
                setValue('bairro',     (d.bairro    || '').toUpperCase());
                setValue('cidade',     (d.localidade|| '').toUpperCase());
                setValue('uf',         (d.uf        || '').toUpperCase());

                // Foca no campo número para o usuário digitar
                var numEl = document.getElementById('numero');
                if (numEl && !numEl.value) numEl.focus();
            })
            .catch(function() { if (spinner) spinner.style.display = 'none'; });
    }

    function setValue(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val;
    }
})();
</script>
@endpush
