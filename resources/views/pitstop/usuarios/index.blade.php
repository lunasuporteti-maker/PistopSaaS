@extends('adminlte::page')
@section('title', 'Usuários do Sistema')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-users-cog mr-2 text-danger"></i>Usuários do Sistema</h1>
        <small class="text-muted">Gerenciamento de acessos e permissões</small>
    </div>
    @can('gerente_ou_admin')
    <a href="{{ route('usuarios.create') }}" class="btn btn-danger btn-sm px-3 shadow-sm">
        <i class="fas fa-user-plus mr-1"></i> Novo Usuário
    </a>
    @endcan
</div>
@endsection

@section('content')
@include('pitstop._partials.alerts')

{{-- Filtros --}}
<div class="card card-outline card-danger shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline flex-wrap gap-2">
            <div class="input-group input-group-sm mr-2 mb-1">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                <input type="text" name="busca" class="form-control" placeholder="Buscar por nome ou e-mail..." value="{{ request('busca') }}" style="width:220px">
            </div>
            <select name="perfil" class="form-control form-control-sm mr-2 mb-1" style="width:140px">
                <option value="">Todos os perfis</option>
                <option value="admin"    {{ request('perfil') === 'admin'    ? 'selected' : '' }}>Administrador</option>
                <option value="gerente"  {{ request('perfil') === 'gerente'  ? 'selected' : '' }}>Gerente</option>
                <option value="operador" {{ request('perfil') === 'operador' ? 'selected' : '' }}>Operador</option>
            </select>
            <button type="submit" class="btn btn-sm btn-danger mr-1 mb-1"><i class="fas fa-filter mr-1"></i>Filtrar</button>
            @if(request()->hasAny(['busca','perfil']))
            <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary mb-1"><i class="fas fa-times mr-1"></i>Limpar</a>
            @endif
        </form>
    </div>
</div>

{{-- Tabela --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="bg-gradient-dark text-white">
                <tr>
                    <th class="pl-3">Usuário</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th>Tentativas</th>
                    <th class="text-right pr-3" width="160">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                <tr class="{{ $u->estaBloqueado() ? 'table-warning' : '' }}">
                    <td class="pl-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-{{ $u->perfil === 'admin' ? 'danger' : ($u->perfil === 'gerente' ? 'warning' : 'info') }} text-white mr-2">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div>
                                <strong>{{ $u->name }}</strong>
                                @if($u->id === auth()->id())
                                <span class="badge badge-light border ml-1"><i class="fas fa-user mr-1"></i>você</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $u->email }}</td>
                    <td>
                        @php $badge = ['admin'=>['danger','Administrador'],'gerente'=>['warning','Gerente'],'operador'=>['info','Operador']] @endphp
                        <span class="badge badge-{{ $badge[$u->perfil][0] ?? 'secondary' }} px-2 py-1">
                            <i class="fas fa-{{ $u->perfil === 'admin' ? 'crown' : ($u->perfil === 'gerente' ? 'user-tie' : 'user') }} mr-1"></i>
                            {{ $badge[$u->perfil][1] ?? ucfirst($u->perfil) }}
                        </span>
                    </td>
                    <td>
                        @if($u->estaBloqueado())
                            <span class="badge badge-warning"><i class="fas fa-lock mr-1"></i>Bloqueado</span>
                            <br><small class="text-muted">até {{ $u->bloqueado_ate->format('H:i') }}</small>
                        @elseif($u->ativo)
                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Ativo</span>
                        @else
                            <span class="badge badge-secondary"><i class="fas fa-ban mr-1"></i>Inativo</span>
                        @endif
                    </td>
                    <td>
                        @if($u->tentativas_login > 0)
                        <span class="badge badge-{{ $u->tentativas_login >= 3 ? 'danger' : 'warning' }}">
                            {{ $u->tentativas_login }}/3
                        </span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-right pr-3">
                        @can('gerente_ou_admin')
                            @if($u->estaBloqueado())
                            <form method="POST" action="{{ route('usuarios.desbloquear', $u) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-xs btn-warning" title="Desbloquear usuário">
                                    <i class="fas fa-unlock"></i>
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('usuarios.edit', $u) }}" class="btn btn-xs btn-outline-primary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endcan
                        @can('admin')
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('usuarios.destroy', $u) }}" class="d-inline"
                                  onsubmit="return confirm('Excluir o usuário {{ addslashes($u->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fas fa-users fa-2x mb-2 d-block text-light"></i>
                        Nenhum usuário encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($usuarios->hasPages())
    <div class="card-footer bg-white py-2">{{ $usuarios->links() }}</div>
    @endif
</div>
@endsection

@push('css')
<style>
.avatar-circle {
    width: 34px; height: 34px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.85rem;
    flex-shrink: 0;
}
.btn-xs { padding: 2px 7px; font-size: 0.75rem; }
.bg-gradient-dark { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); }
</style>
@endpush
