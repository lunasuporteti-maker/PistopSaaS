@extends('adminlte::page')
@section('title', $peca->exists ? 'Editar Peça' : 'Nova Peça')

@section('content_header')
    <h1>{{ $peca->exists ? 'Editar Peça' : 'Nova Peça' }}</h1>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="card card-danger card-outline" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ $peca->exists ? route('pecas.update', $peca) : route('pecas.store') }}">
            @csrf
            @if($peca->exists) @method('PUT') @endif

            <div class="form-group">
                <label>Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                       value="{{ old('nome', $peca->nome) }}" required>
                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Quantidade em Estoque</label>
                        <input type="number" name="quantidade" class="form-control" value="{{ old('quantidade', $peca->quantidade) }}" min="0">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Estoque Mínimo</label>
                        <input type="number" name="estoque_minimo" class="form-control" value="{{ old('estoque_minimo', $peca->estoque_minimo) }}" min="0">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Preço de Custo (R$)</label>
                        <input type="number" step="0.01" name="preco_custo" class="form-control" value="{{ old('preco_custo', $peca->preco_custo) }}" min="0">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Preço de Venda (R$)</label>
                        <input type="number" step="0.01" name="preco_venda" class="form-control" value="{{ old('preco_venda', $peca->preco_venda) }}" min="0">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Salvar</button>
            <a href="{{ route('pecas.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </form>
    </div>
</div>
@endsection
