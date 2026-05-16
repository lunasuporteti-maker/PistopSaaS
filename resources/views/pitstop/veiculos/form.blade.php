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
                {{-- Cliente com botão novo --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Cliente <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select id="cliente_id" name="cliente_id" class="form-control @error('cliente_id') is-invalid @enderror" required>
                                <option value="">Selecione o cliente...</option>
                                @foreach($clientes as $c)
                                <option value="{{ $c->id }}" {{ old('cliente_id', $veiculo->cliente_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->nome }}
                                </option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#modalNovoCliente" title="Adicionar novo cliente">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            </div>
                        </div>
                        @error('cliente_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Placa <span class="text-danger">*</span></label>
                        <input type="text" name="placa"
                               class="form-control @error('placa') is-invalid @enderror"
                               value="{{ old('placa', $veiculo->placa) }}"
                               placeholder="ABC1234" maxlength="7" data-placa required>
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
                               placeholder="{{ date('Y') }}" maxlength="4" data-ano inputmode="numeric">
                    </div>
                </div>

                {{-- Marca com select --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Marca <span class="text-danger">*</span></label>
                        <select id="sel-marca" name="marca" class="form-control @error('marca') is-invalid @enderror" required>
                            <option value="">Selecione a marca...</option>
                            @foreach(['Agrale','BMW','BYD','Caoa Chery','Chevrolet','Chrysler','Citroën','Dodge','Fiat','Ford','GWM','Honda','Hyundai','JAC','Jeep','Kia','Land Rover','Lexus','Mercedes-Benz','Mitsubishi','Nissan','Peugeot','Renault','Subaru','Suzuki','Toyota','Troller','Volkswagen','Volvo'] as $marca)
                            <option value="{{ $marca }}" {{ old('marca', $veiculo->marca) === $marca ? 'selected' : '' }}>{{ $marca }}</option>
                            @endforeach
                        </select>
                        @error('marca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Modelo com autocomplete --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Modelo <span class="text-danger">*</span></label>
                        <input type="text" name="modelo" id="inp-modelo"
                               class="form-control @error('modelo') is-invalid @enderror"
                               value="{{ old('modelo', $veiculo->modelo) }}"
                               placeholder="Selecione a marca primeiro..."
                               list="lista-modelos"
                               maxlength="60" data-uppercase required autocomplete="off">
                        <datalist id="lista-modelos"></datalist>
                        @error('modelo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label>Cor</label>
                        <input type="text" name="cor"
                               class="form-control"
                               value="{{ old('cor', $veiculo->cor) }}"
                               placeholder="Branco" maxlength="40" data-uppercase data-no-special>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>KM Atual</label>
                        <input type="text" name="km_atual"
                               class="form-control"
                               value="{{ old('km_atual', $veiculo->km_atual) }}"
                               placeholder="0" maxlength="8" data-km inputmode="numeric">
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-save mr-1"></i> Salvar Veículo
                </button>
                <a href="{{ route('veiculos.index') }}" class="btn btn-outline-secondary ml-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Novo Cliente --}}
<div class="modal fade" id="modalNovoCliente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Novo Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="modal-alert" class="alert alert-danger d-none"></div>
                <div class="form-group">
                    <label>Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" id="mc-nome" class="form-control" placeholder="NOME DO CLIENTE" data-uppercase required>
                </div>
                <div class="form-group">
                    <label>Telefone / WhatsApp</label>
                    <input type="text" id="mc-telefone" class="form-control" placeholder="(99) 99999-9999" data-phone>
                </div>
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text" id="mc-cpf" class="form-control" placeholder="000.000.000-00" data-cpf>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-salvar-cliente" class="btn btn-danger">
                    <i class="fas fa-save mr-1"></i> Salvar Cliente
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
// ── Modelos por marca ──────────────────────────────────────────────────────
var MODELOS = {
    'Chevrolet': ['Onix','Onix Plus','Tracker','Cruze','S10','Trailblazer','Spin','Montana','Prisma','Cobalt','Celta','Classic'],
    'Fiat':      ['Argo','Cronos','Mobi','Pulse','Fastback','Strada','Toro','Uno','Palio','Siena','Bravo','Punto','500','Ducato'],
    'Volkswagen':['Gol','Polo','Virtus','T-Cross','Nivus','Taos','Saveiro','Amarok','Fox','Voyage','Jetta','Passat','Tiguan'],
    'Ford':      ['Ka','Ka+','EcoSport','Territory','Ranger','Maverick','Bronco Sport','Fiesta','Focus','Edge','Fusion','F-150'],
    'Toyota':    ['Corolla','Corolla Cross','Hilux','SW4','Yaris','RAV4','Camry','Prius','Land Cruiser'],
    'Honda':     ['Civic','City','HR-V','CR-V','WR-V','Fit','Accord','Pilot','Ridgeline'],
    'Hyundai':   ['HB20','HB20S','Creta','Tucson','Santa Fe','ix35','Elantra','Azera','i30'],
    'Renault':   ['Kwid','Logan','Sandero','Duster','Captur','Oroch','Zoe','Clio','Megane'],
    'Jeep':      ['Renegade','Compass','Commander','Grand Cherokee','Wrangler','Gladiator'],
    'Nissan':    ['Kicks','Versa','Frontier','Sentra','Altima','Leaf','March','Livina'],
    'Peugeot':   ['208','308','2008','3008','5008','Partner','Expert'],
    'Citroën':   ['C3','C4','C4 Cactus','Aircross','Berlingo','Jumper'],
    'Mitsubishi':['L200','ASX','Eclipse Cross','Outlander','Pajero','Pajero Sport'],
    'Kia':       ['Sportage','Stinger','Sorento','Carnival','Cerato','Picanto','Soul','Niro'],
    'BMW':       ['116i','118i','120i','320i','328i','330i','520i','X1','X3','X5','X6','Z4','M3'],
    'Mercedes-Benz':['A 200','C 180','C 200','E 250','GLA 200','GLC 250','GLE 400','Sprinter'],
    'Volkswagen':['Gol','Polo','Virtus','T-Cross','Nivus','Taos','Saveiro','Amarok'],
    'Subaru':    ['Impreza','Forester','Outback','XV','WRX','BRZ','Legacy'],
    'Suzuki':    ['Jimny','Swift','S-Cross','Vitara','Grand Vitara'],
    'Land Rover':['Defender','Discovery','Discovery Sport','Range Rover','Evoque'],
    'BYD':       ['Dolphin','Seal','Han','Song Plus','Yuan Plus','Atto 3'],
};

document.addEventListener('DOMContentLoaded', function () {
    var selMarca  = document.getElementById('sel-marca');
    var inpModelo = document.getElementById('inp-modelo');
    var datalist  = document.getElementById('lista-modelos');

    function atualizarModelos() {
        var marca = selMarca ? selMarca.value : '';
        datalist.innerHTML = '';
        if (marca && MODELOS[marca]) {
            MODELOS[marca].forEach(function (m) {
                var opt = document.createElement('option');
                opt.value = m.toUpperCase();
                datalist.appendChild(opt);
            });
            if (inpModelo) inpModelo.placeholder = 'Digite ou escolha o modelo...';
        } else {
            if (inpModelo) inpModelo.placeholder = 'Selecione a marca primeiro...';
        }
    }

    if (selMarca) {
        selMarca.addEventListener('change', function () {
            if (inpModelo) inpModelo.value = '';
            atualizarModelos();
        });
        atualizarModelos(); // popula ao carregar (modo edição)
    }

    // ── Modal novo cliente ───────────────────────────────────────────────
    var btnSalvar = document.getElementById('btn-salvar-cliente');
    if (btnSalvar) {
        btnSalvar.addEventListener('click', function () {
            var nome     = document.getElementById('mc-nome').value.trim();
            var telefone = document.getElementById('mc-telefone').value.trim();
            var cpf      = document.getElementById('mc-cpf').value.trim();
            var alert    = document.getElementById('modal-alert');

            if (!nome) {
                alert.textContent = 'O nome é obrigatório.';
                alert.classList.remove('d-none');
                return;
            }
            alert.classList.add('d-none');
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...';

            fetch('{{ route("json.clientes.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]') ?
                        document.querySelector('meta[name=csrf-token]').content : '{{ csrf_token() }}'
                },
                body: JSON.stringify({ nome: nome, telefone: telefone, cpf: cpf })
            })
            .then(function(r) { return r.json(); })
            .then(function(cliente) {
                if (cliente.id) {
                    var sel = document.getElementById('cliente_id');
                    var opt = document.createElement('option');
                    opt.value    = cliente.id;
                    opt.text     = cliente.nome;
                    opt.selected = true;
                    sel.appendChild(opt);
                    $('#modalNovoCliente').modal('hide');
                    document.getElementById('mc-nome').value     = '';
                    document.getElementById('mc-telefone').value = '';
                    document.getElementById('mc-cpf').value      = '';
                } else {
                    alert.textContent = cliente.message || 'Erro ao salvar cliente.';
                    alert.classList.remove('d-none');
                }
            })
            .catch(function() {
                alert.textContent = 'Erro de conexão. Tente novamente.';
                alert.classList.remove('d-none');
            })
            .finally(function() {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = '<i class="fas fa-save mr-1"></i> Salvar Cliente';
            });
        });
    }

    // Aplica máscaras ao modal quando abrir
    $('#modalNovoCliente').on('shown.bs.modal', function () {
        if (window.pitStopAplicarMascaras) pitStopAplicarMascaras();
        document.getElementById('mc-nome').focus();
    });
});
</script>
@endpush
