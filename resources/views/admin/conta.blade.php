@extends('layouts.admin')
@section('title', 'Minha Conta')
@section('page_title', 'Minha Conta — Super Admin')

@section('content')

@if(session('success'))
    <div class="alert alert-success py-2 mb-3">{{ session('success') }}</div>
@endif

<div class="row">

{{-- Dados do perfil --}}
<div class="col-md-5 mb-4">
<div class="adm-card">
    <h5 class="mb-4" style="font-weight:700;">
        <i class="fas fa-user mr-2" style="color:var(--adm-accent)"></i>Dados da Conta
    </h5>

    <form action="{{ route('admin.conta.perfil') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label style="font-size:.8rem;color:var(--adm-muted)">Nome</label>
            <input type="text" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', auth()->user()->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label style="font-size:.8rem;color:var(--adm-muted)">Email</label>
            <input type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', auth()->user()->email) }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label style="font-size:.8rem;color:var(--adm-muted)">Usuário</label>
            <input type="text" class="form-control" value="{{ auth()->user()->username }}" disabled>
            <small style="color:var(--adm-muted);font-size:.75rem;">Username não pode ser alterado</small>
        </div>

        <button type="submit" class="btn btn-danger btn-block mt-1">
            <i class="fas fa-save mr-1"></i> Salvar dados
        </button>
    </form>
</div>
</div>

{{-- Alterar senha --}}
<div class="col-md-5 mb-4">
<div class="adm-card">
    <h5 class="mb-4" style="font-weight:700;">
        <i class="fas fa-key mr-2" style="color:var(--adm-accent)"></i>Alterar Senha
    </h5>

    <form action="{{ route('admin.conta.senha') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label style="font-size:.8rem;color:var(--adm-muted)">Senha atual</label>
            <input type="password" name="senha_atual"
                   class="form-control @error('senha_atual') is-invalid @enderror"
                   autocomplete="current-password" required>
            @error('senha_atual')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label style="font-size:.8rem;color:var(--adm-muted)">Nova senha</label>
            <input type="password" name="senha_nova"
                   class="form-control @error('senha_nova') is-invalid @enderror"
                   autocomplete="new-password" required minlength="8">
            @error('senha_nova')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label style="font-size:.8rem;color:var(--adm-muted)">Confirmar nova senha</label>
            <input type="password" name="senha_nova_confirmation"
                   class="form-control" autocomplete="new-password" required minlength="8">
        </div>

        <button type="submit" class="btn btn-danger btn-block mt-1">
            <i class="fas fa-save mr-1"></i> Salvar nova senha
        </button>
    </form>
</div>
</div>

</div>
@endsection
