@extends('adminlte::page')
@section('title', 'Meu Perfil')

@section('content_header')
<div class="d-flex align-items-center">
    <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-user-circle mr-2 text-danger"></i>Meu Perfil</h1>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="row justify-content-center">
    <div class="col-md-6">

        {{-- Editar dados da conta --}}
        <div class="card card-outline card-danger shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-id-card mr-2"></i>Dados da Conta</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('perfil.update.dados') }}">
                    @csrf @method('PATCH')
                    <div class="form-group">
                        <label class="font-weight-600">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $usuario->name) }}" required maxlength="100">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-600">Login (username) <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username', $usuario->username) }}" required maxlength="50">
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-600">E-mail</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $usuario->email) }}" maxlength="100">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-muted">Perfil</label>
                        @php $badge = ['admin'=>['danger','Administrador'],'gerente'=>['warning','Gerente'],'operador'=>['info','Operador'],'mecanico'=>['success','Mecânico']] @endphp
                        <div>
                            <span class="badge badge-{{ $badge[$usuario->perfil][0] ?? 'secondary' }} px-2">
                                {{ $badge[$usuario->perfil][1] ?? ucfirst($usuario->perfil) }}
                            </span>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save mr-1"></i> Salvar Dados
                    </button>
                </form>
            </div>
        </div>

        {{-- Troca de senha --}}
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-key mr-2"></i>Alterar Senha</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('perfil.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="font-weight-600">Senha Atual <span class="text-danger">*</span></label>
                        <input type="password" name="senha_atual"
                               class="form-control @error('senha_atual') is-invalid @enderror"
                               placeholder="••••••••" required>
                        @error('senha_atual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-600">Nova Senha <span class="text-danger">*</span></label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="••••••••" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">
                            Mínimo 8 caracteres · letras maiúsculas e minúsculas · número · caractere especial (!@#$...)
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-600">Confirmar Nova Senha <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation"
                               class="form-control"
                               placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fas fa-save mr-2"></i>Salvar Nova Senha
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
