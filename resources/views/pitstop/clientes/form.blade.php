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

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" name="nome"
                               class="form-control @error('nome') is-invalid @enderror"
                               value="{{ old('nome', $cliente->nome) }}"
                               placeholder="NOME DO CLIENTE"
                               maxlength="120"
                               data-uppercase
                               data-no-special
                               required autocomplete="off">
                        <small class="text-muted">Apenas letras. Será salvo em maiúsculas.</small>
                        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Telefone / WhatsApp</label>
                        <input type="text" name="telefone"
                               class="form-control"
                               value="{{ old('telefone', $cliente->telefone) }}"
                               placeholder="(99) 99999-9999"
                               maxlength="20"
                               data-phone>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" name="cpf"
                               class="form-control @error('cpf') is-invalid @enderror"
                               value="{{ old('cpf', $cliente->cpf) }}"
                               placeholder="000.000.000-00"
                               maxlength="14"
                               data-cpf>
                        @error('cpf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $cliente->email) }}"
                               placeholder="cliente@email.com"
                               maxlength="120">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Endereço</label>
                        <input type="text" name="endereco"
                               class="form-control"
                               value="{{ old('endereco', $cliente->endereco) }}"
                               placeholder="Rua, número, bairro"
                               maxlength="200"
                               data-uppercase>
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-save mr-1"></i> Salvar Cliente
                </button>
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary ml-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
