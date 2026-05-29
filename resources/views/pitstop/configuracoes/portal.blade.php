@extends('layouts.pitstop')
@section('title', 'Configurações do Portal')

@section('content_header')
<h1 class="m-0 font-weight-bold"><i class="fas fa-cog mr-2"></i>Portal Público — Configurações</h1>
@endsection

@section('content')
<div class="row">
<div class="col-md-6">
<div class="card shadow">
<div class="card-body">

    <form action="{{ route('configuracoes.portal.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="d-flex align-items-center" style="gap:10px;cursor:pointer">
                <input type="checkbox" name="aprovacao_online_ativa" value="1" {{ $aprovacaoAtiva ? 'checked' : '' }}>
                <span><strong>Aprovação online ativa</strong><br>
                <small class="text-muted">Clientes podem aprovar orçamentos pelo portal sem contato por telefone</small></span>
            </label>
        </div>

        <hr>
        <h6 class="font-weight-bold mb-3">Notificações</h6>

        <div class="form-group">
            <label class="d-flex align-items-center" style="gap:10px;cursor:pointer">
                <input type="checkbox" name="canal_email" value="1" {{ ($canais['email'] ?? true) ? 'checked' : '' }}>
                <span>Receber notificações por <strong>email</strong></span>
            </label>
        </div>

        <div class="form-group">
            <label class="font-weight-bold" style="font-size:.85rem">Quem recebe as notificações</label>
            <small class="text-muted d-block mb-2">Sem seleção = todos os admins e gerentes recebem</small>
            @foreach($usuarios as $user)
            <div>
                <label class="d-flex align-items-center" style="gap:8px;cursor:pointer;margin-bottom:4px">
                    <input type="checkbox" name="notificar_usuarios_ids[]"
                           value="{{ $user->id }}"
                           {{ in_array($user->id, $notificarIds) ? 'checked' : '' }}>
                    <span style="font-size:.85rem">{{ $user->name }} <small class="text-muted">({{ $user->perfil }})</small></span>
                </label>
            </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-save mr-1"></i> Salvar configurações
        </button>
    </form>

</div>
</div>
</div>
</div>
@endsection
