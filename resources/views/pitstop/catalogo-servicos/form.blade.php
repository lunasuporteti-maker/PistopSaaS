@extends('layouts.pitstop')
@section('title', $servico->exists ? 'Editar Serviço' : 'Novo Serviço no Catálogo')

@section('content_header')
    <h1>{{ $servico->exists ? 'Editar Serviço' : 'Novo Serviço no Catálogo' }}</h1>
@endsection

@section('content')

<div class="card card-danger card-outline" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ $servico->exists ? route('catalogo-servicos.update', $servico) : route('catalogo-servicos.store') }}">
            @csrf
            @if($servico->exists) @method('PUT') @endif

            <div class="form-group">
                <label>Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                       value="{{ old('nome', $servico->nome) }}" required maxlength="100">
                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $servico->descricao) }}</textarea>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Preço Sugerido (R$)</label>
                        <input type="number" step="0.01" name="preco_sugerido" class="form-control"
                               value="{{ old('preco_sugerido', $servico->preco_sugerido) }}" min="0">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Tempo Estimado (horas)</label>
                        <input type="number" step="0.1" name="tempo_estimado_horas" class="form-control"
                               value="{{ old('tempo_estimado_horas', $servico->tempo_estimado_horas) }}" min="0" max="999">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Lembrete automático (dias após serviço)</label>
                        <input type="number" name="dias_lembrete" class="form-control"
                               value="{{ old('dias_lembrete', $servico->dias_lembrete) }}" min="1" max="3650"
                               placeholder="Ex: 180 (6 meses)">
                        <small class="text-muted">Deixe vazio para não gerar lembrete.</small>
                    </div>
                </div>
                @if($servico->exists)
                <div class="col-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="ativo" class="form-control">
                            <option value="1" {{ $servico->ativo ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ !$servico->ativo ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>
                </div>
                @endif
            </div>

            <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Salvar</button>
            <a href="{{ route('catalogo-servicos.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </form>
    </div>
</div>
@endsection
