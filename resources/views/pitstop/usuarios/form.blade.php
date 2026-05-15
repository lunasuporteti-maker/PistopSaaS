@extends('adminlte::page')
@section('title', $usuario->exists ? 'Editar Usuário' : 'Novo Usuário')

@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="m-0 font-weight-bold text-dark">
            <i class="fas fa-{{ $usuario->exists ? 'user-edit' : 'user-plus' }} mr-2 text-danger"></i>
            {{ $usuario->exists ? 'Editar Usuário' : 'Novo Usuário' }}
        </h1>
        <small class="text-muted">{{ $usuario->exists ? 'Altere os dados do usuário abaixo' : 'Preencha os dados para criar o acesso' }}</small>
    </div>
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-danger text-white py-3">
                <h5 class="mb-0"><i class="fas fa-id-card mr-2"></i>Dados do Usuário</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ $usuario->exists ? route('usuarios.update', $usuario) : route('usuarios.store') }}">
                    @csrf
                    @if($usuario->exists) @method('PUT') @endif

                    <div class="form-group">
                        <label class="font-weight-600 text-dark">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control form-control-lg @error('name') is-invalid @enderror"
                               value="{{ old('name', $usuario->name) }}"
                               placeholder="NOME COMPLETO DO USUÁRIO"
                               maxlength="100"
                               data-uppercase
                               data-no-special
                               required autocomplete="off">
                        <small class="text-muted">Apenas letras e espaços. Será salvo em maiúsculas.</small>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-600 text-dark">E-mail <span class="text-danger">*</span></label>
                        <input type="email" name="email"
                               class="form-control form-control-lg @error('email') is-invalid @enderror"
                               value="{{ old('email', $usuario->email) }}"
                               placeholder="usuario@email.com"
                               maxlength="120"
                               required autocomplete="off">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-600 text-dark">Perfil de Acesso <span class="text-danger">*</span></label>
                        <select name="perfil" class="form-control form-control-lg @error('perfil') is-invalid @enderror" required>
                            @foreach($perfisDisponiveis as $valor => $label)
                            <option value="{{ $valor }}" {{ old('perfil', $usuario->perfil) === $valor ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            @if(auth()->user()->isAdmin())
                                <strong>Operador</strong>: Fila e Agendamentos &nbsp;|&nbsp;
                                <strong>Gerente</strong>: Tudo exceto Usuários &nbsp;|&nbsp;
                                <strong>Admin</strong>: Acesso total
                            @else
                                Você pode criar apenas usuários com perfil <strong>Operador</strong>.
                            @endif
                        </small>
                        @error('perfil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-600 text-dark">
                                    Senha {{ $usuario->exists ? '<small class="font-weight-normal text-muted">(deixe em branco para não alterar)</small>' : '<span class="text-danger">*</span>' }}
                                </label>
                                <input type="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Mínimo 6 caracteres"
                                       {{ !$usuario->exists ? 'required' : '' }}
                                       autocomplete="new-password">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-600 text-dark">Confirmar Senha</label>
                                <input type="password" name="password_confirmation"
                                       class="form-control"
                                       placeholder="Repita a senha"
                                       autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    @if($usuario->exists)
                    <div class="form-group">
                        <label class="font-weight-600 text-dark">Status</label>
                        <select name="ativo" class="form-control @error('ativo') is-invalid @enderror">
                            <option value="1" {{ old('ativo', $usuario->ativo ? 1 : 0) == 1 ? 'selected' : '' }}>
                                ✅ Ativo
                            </option>
                            <option value="0" {{ old('ativo', $usuario->ativo ? 1 : 0) == 0 ? 'selected' : '' }}>
                                ⛔ Inativo
                            </option>
                        </select>
                    </div>

                    @if($usuario->estaBloqueado())
                    <div class="alert alert-warning d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-lock mr-2"></i>
                            <strong>Conta Bloqueada</strong> — Tentativas: {{ $usuario->tentativas_login }}/3
                            <br><small>Bloqueado até {{ $usuario->bloqueado_ate->format('d/m/Y H:i') }}</small>
                        </div>
                        <form method="POST" action="{{ route('usuarios.desbloquear', $usuario) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning">
                                <i class="fas fa-unlock mr-1"></i> Desbloquear
                            </button>
                        </form>
                    </div>
                    @endif
                    @endif

                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fas fa-save mr-1"></i>
                            {{ $usuario->exists ? 'Salvar Alterações' : 'Criar Usuário' }}
                        </button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary ml-2">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
.font-weight-600 { font-weight: 600; }
.bg-gradient-danger { background: linear-gradient(135deg, #c0392b, #e74c3c) !important; }
</style>
@endpush
