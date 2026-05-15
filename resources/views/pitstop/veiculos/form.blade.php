@extends('adminlte::page')
@section('title', $veiculo->exists ? 'Editar Veículo' : 'Novo Veículo')

@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('veiculos.index') }}" class="btn btn-sm btn-outline-secondary mr-3"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="m-0 font-weight-bold text-dark">
            <i class="fas fa-car mr-2 text-danger"></i>
            {{ $veiculo->exists ? 'Editar Veículo' : 'Novo Veículo' }}
        </h1>
    </div>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="card card-outline card-danger shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ $veiculo->exists ? route('veiculos.update', $veiculo) : route('veiculos.store') }}">
            @csrf
            @if($veiculo->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Cliente <span class="text-danger">*</span></label>
                        <select name="cliente_id" class="form-control @error('cliente_id') is-invalid @enderror" required>
                            <option value="">Selecione o cliente...</option>
                            @foreach($clientes as $c)
                            <option value="{{ $c->id }}" {{ old('cliente_id', $veiculo->cliente_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->nome }}
                            </option>
                            @endforeach
                        </select>
                        @error('cliente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Placa <span class="text-danger">*</span></label>
                        <input type="text" name="placa"
                               class="form-control @error('placa') is-invalid @enderror"
                               value="{{ old('placa', $veiculo->placa) }}"
                               placeholder="ABC1234"
                               maxlength="7"
                               data-placa
                               required>
                        <small class="text-muted">Sem espaços ou hífen</small>
                        @error('placa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Ano</label>
                        <input type="text" name="ano"
                               class="form-control"
                               value="{{ old('ano', $veiculo->ano) }}"
                               placeholder="{{ date('Y') }}"
                               maxlength="4"
                               data-ano
                               inputmode="numeric">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Marca <span class="text-danger">*</span></label>
                        <select name="marca" class="form-control @error('marca') is-invalid @enderror" required>
                            <option value="">Selecione a marca...</option>
                            @foreach(['Agrale','BMW','BYD','Caoa Chery','Chevrolet','Chrysler','Citroën','Dodge','Fiat','Ford','GWM','Honda','Hyundai','JAC','Jeep','Kia','Land Rover','Lexus','Mercedes-Benz','Mitsubishi','Nissan','Peugeot','Renault','Subaru','Suzuki','Toyota','Troller','Volkswagen','Volvo'] as $marca)
                            <option value="{{ $marca }}" {{ old('marca', $veiculo->marca) === $marca ? 'selected' : '' }}>{{ $marca }}</option>
                            @endforeach
                        </select>
                        @error('marca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Modelo <span class="text-danger">*</span></label>
                        <input type="text" name="modelo"
                               class="form-control @error('modelo') is-invalid @enderror"
                               value="{{ old('modelo', $veiculo->modelo) }}"
                               placeholder="Civic, Corolla..."
                               maxlength="60"
                               data-uppercase
                               required>
                        @error('modelo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Cor</label>
                        <input type="text" name="cor"
                               class="form-control"
                               value="{{ old('cor', $veiculo->cor) }}"
                               placeholder="Branco"
                               maxlength="40"
                               data-uppercase
                               data-no-special>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>KM Atual</label>
                        <input type="text" name="km_atual"
                               class="form-control"
                               value="{{ old('km_atual', $veiculo->km_atual) }}"
                               placeholder="0"
                               maxlength="8"
                               data-km
                               inputmode="numeric">
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-save mr-1"></i> Salvar Veículo
                </button>
                <a href="{{ route('veiculos.index') }}" class="btn btn-outline-secondary ml-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
