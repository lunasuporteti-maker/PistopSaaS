@extends('layouts.admin')
@section('title', 'Minha Conta')
@section('page_title', 'Minha Conta — Super Admin')

@section('content')

<div class="row justify-content-center">
<div class="col-md-5">
<div class="adm-card">

    <h5 class="mb-4" style="font-weight:700;">
        <i class="fas fa-key mr-2" style="color:var(--adm-accent)"></i>Alterar Senha
    </h5>

    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.conta.senha') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label style="font-size:.8rem;color:var(--adm-muted)">Senha atual</label>
            <input type="password" name="senha_atual" class="form-control @error('senha_atual') is-invalid @enderror"
                   autocomplete="current-password" required>
            @error('senha_atual')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label style="font-size:.8rem;color:var(--adm-muted)">Nova senha</label>
            <input type="password" name="senha_nova" class="form-control @error('senha_nova') is-invalid @enderror"
                   autocomplete="new-password" required minlength="8">
            @error('senha_nova')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label style="font-size:.8rem;color:var(--adm-muted)">Confirmar nova senha</label>
            <input type="password" name="senha_nova_confirmation" class="form-control"
                   autocomplete="new-password" required minlength="8">
        </div>

        <button type="submit" class="btn btn-danger btn-block mt-2">
            <i class="fas fa-save mr-1"></i> Salvar nova senha
        </button>
    </form>

</div>

<div class="adm-card mt-3">
    <h6 style="font-weight:600;margin-bottom:8px;">
        <i class="fas fa-user mr-2" style="color:var(--adm-muted)"></i>Dados da conta
    </h6>
    <div style="font-size:.85rem;color:var(--adm-muted)">
        <div><strong>Usuário:</strong> {{ auth()->user()->username }}</div>
        <div><strong>Nome:</strong> {{ auth()->user()->name }}</div>
        <div><strong>Email:</strong> {{ auth()->user()->email }}</div>
        <div><strong>Perfil:</strong> super_admin</div>
    </div>
</div>

</div>
</div>

@endsection
