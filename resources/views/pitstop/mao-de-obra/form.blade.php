@extends('adminlte::page')
@section('title', $item->exists ? 'Editar Mão de Obra' : 'Nova Mão de Obra')

@section('content_header')
    <h1>{{ $item->exists ? 'Editar Mão de Obra' : 'Nova Mão de Obra' }}</h1>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="card card-danger card-outline" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ $item->exists ? route('mao-de-obra.update', $item) : route('mao-de-obra.store') }}">
            @csrf
            @if($item->exists) @method('PUT') @endif

            <div class="form-group">
                <label>Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                       value="{{ old('nome', $item->nome) }}" required maxlength="200">
                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $item->descricao) }}</textarea>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Preço (R$) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="preco" class="form-control @error('preco') is-invalid @enderror"
                               value="{{ old('preco', $item->preco) }}" min="0" required>
                        @error('preco')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Tempo Estimado (horas)</label>
                        <input type="number" step="0.1" name="tempo_estimado_horas" class="form-control"
                               value="{{ old('tempo_estimado_horas', $item->tempo_estimado_horas) }}" min="0" max="999">
                    </div>
                </div>
            </div>

            @if($item->exists)
            <div class="form-group">
                <label>Status</label>
                <select name="ativo" class="form-control">
                    <option value="1" {{ $item->ativo ? 'selected' : '' }}>Ativo</option>
                    <option value="0" {{ !$item->ativo ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>
            @endif

            <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Salvar</button>
            <a href="{{ route('mao-de-obra.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </form>
    </div>
</div>
@endsection
