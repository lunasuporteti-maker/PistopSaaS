@extends('layouts.pitstop')
@section('title', $funcionario->exists ? 'Editar Funcionário' : 'Novo Funcionário')

@section('content_header')
    <h1>{{ $funcionario->exists ? 'Editar Funcionário' : 'Novo Funcionário' }}</h1>
@endsection

@section('content')

<div class="card card-danger card-outline" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ $funcionario->exists ? route('funcionarios.update', $funcionario) : route('funcionarios.store') }}">
            @csrf
            @if($funcionario->exists) @method('PUT') @endif

            <div class="form-group">
                <label>Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                       value="{{ old('nome', $funcionario->nome) }}" required>
                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Cargo</label>
                        <input type="text" name="cargo" class="form-control" value="{{ old('cargo', $funcionario->cargo) }}">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" class="form-control" value="{{ old('telefone', $funcionario->telefone) }}">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Salário Base (R$)</label>
                        <input type="number" step="0.01" name="salario_base" class="form-control" value="{{ old('salario_base', $funcionario->salario_base) }}" min="0">
                    </div>
                </div>
                @if($funcionario->exists)
                <div class="col-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="ativo" class="form-control">
                            <option value="1" {{ $funcionario->ativo ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ !$funcionario->ativo ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>
                </div>
                @endif
            </div>

            <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Salvar</button>
            <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </form>
    </div>
</div>
@endsection
