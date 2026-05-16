@extends('adminlte::page')
@section('title', 'Editar OS ' . $ordem->numero_os)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold text-dark">
        <i class="fas fa-edit mr-2 text-danger"></i>Editar OS {{ $ordem->numero_os }}
    </h1>
    <a href="{{ route('ordens.show', $ordem) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Voltar
    </a>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-outline card-danger shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    {{ $ordem->cliente->nome }} — {{ $ordem->veiculo->marca }} {{ $ordem->veiculo->modelo }}
                    <span class="badge badge-secondary ml-2">{{ $ordem->veiculo->placa }}</span>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('ordens.update', $ordem) }}">
                    @csrf @method('PUT')

                    <div class="form-group">
                        <label class="font-weight-600">Descrição do Serviço</label>
                        <textarea name="descricao" class="form-control @error('descricao') is-invalid @enderror"
                                  rows="4">{{ old('descricao', $ordem->descricao) }}</textarea>
                        @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-600">Valor Total (R$) <span class="text-danger">*</span></label>
                                <input type="number" name="valor_total" step="0.01" min="0"
                                       class="form-control @error('valor_total') is-invalid @enderror"
                                       value="{{ old('valor_total', $ordem->valor_total) }}" required>
                                @error('valor_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-600">Garantia (dias)</label>
                                <input type="number" name="garantia_dias" min="0"
                                       class="form-control @error('garantia_dias') is-invalid @enderror"
                                       value="{{ old('garantia_dias', $ordem->garantia_dias) }}"
                                       placeholder="0">
                                @error('garantia_dias')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <a href="{{ route('ordens.show', $ordem) }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-save mr-1"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
