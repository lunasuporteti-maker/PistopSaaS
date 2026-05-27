@extends('layouts.pitstop')
@section('title', $parceiro->exists ? 'Editar Parceiro' : 'Novo Parceiro')

@section('content_header')
    <h1>{{ $parceiro->exists ? 'Editar Parceiro' : 'Novo Parceiro' }}</h1>
@endsection

@section('content')

<div class="card card-danger card-outline" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ $parceiro->exists ? route('parceiros.update', $parceiro) : route('parceiros.store') }}">
            @csrf
            @if($parceiro->exists) @method('PUT') @endif

            <div class="form-group">
                <label>Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                       value="{{ old('nome', $parceiro->nome) }}" required>
                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Serviço Prestado</label>
                <input type="text" name="servico_prestado" class="form-control" value="{{ old('servico_prestado', $parceiro->servico_prestado) }}" placeholder="Ex: Funilaria, Elétrica...">
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" class="form-control" value="{{ old('telefone', $parceiro->telefone) }}">
                    </div>
                </div>
                @if($parceiro->exists)
                <div class="col-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="ativo" class="form-control">
                            <option value="1" {{ $parceiro->ativo ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ !$parceiro->ativo ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>
                </div>
                @endif
            </div>

            <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Salvar</button>
            <a href="{{ route('parceiros.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </form>
    </div>
</div>
@endsection
