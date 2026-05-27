@extends('layouts.admin')
@section('title', $tenant->nome)
@section('page_title', $tenant->nome)

@section('content')

<div class="mb-3">
    <a href="{{ route('admin.tenants.index') }}" style="color:var(--adm-muted);font-size:.8rem">
        ← Voltar para clientes
    </a>
</div>

<div class="row">

    {{-- Coluna esquerda: info + ações --}}
    <div class="col-md-4">

        {{-- Info --}}
        <div class="adm-card mb-3">
            <div class="adm-card-title mb-3">Informações</div>
            <table style="width:100%;font-size:.82rem;border-collapse:collapse">
                <tr>
                    <td style="color:var(--adm-muted);padding:.3rem 0;width:45%">Nome</td>
                    <td style="font-weight:600">{{ $tenant->nome }}</td>
                </tr>
                <tr>
                    <td style="color:var(--adm-muted);padding:.3rem 0">Slug</td>
                    <td style="font-family:monospace">{{ $tenant->slug }}</td>
                </tr>
                <tr>
                    <td style="color:var(--adm-muted);padding:.3rem 0">Plano</td>
                    <td>{{ ucfirst($tenant->plano) }}</td>
                </tr>
                <tr>
                    <td style="color:var(--adm-muted);padding:.3rem 0">Domínio</td>
                    <td>{{ $tenant->dominio_customizado ?: '—' }}</td>
                </tr>
                <tr>
                    <td style="color:var(--adm-muted);padding:.3rem 0">Status</td>
                    <td>
                        @php $status = $tenant->statusAssinatura(); @endphp
                        @if(str_starts_with($status, 'Plano'))
                            <span class="badge-pago">{{ $status }}</span>
                        @elseif(str_starts_with($status, 'Trial'))
                            <span class="badge-trial">{{ $status }}</span>
                        @elseif($status === 'Expirado')
                            <span class="badge-expirado">{{ $status }}</span>
                        @else
                            <span class="badge-legado">{{ $status }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="color:var(--adm-muted);padding:.3rem 0">Ativo</td>
                    <td>
                        @if($tenant->ativo)
                            <span class="badge-pago">Sim</span>
                        @else
                            <span class="badge-inativo">Não</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="color:var(--adm-muted);padding:.3rem 0">Criado em</td>
                    <td>{{ $tenant->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @if($tenant->trial_ends_at)
                <tr>
                    <td style="color:var(--adm-muted);padding:.3rem 0">Trial até</td>
                    <td>{{ $tenant->trial_ends_at->format('d/m/Y') }}</td>
                </tr>
                @endif
                @if($tenant->plano_ativo)
                <tr>
                    <td style="color:var(--adm-muted);padding:.3rem 0">Plano vence</td>
                    <td>{{ $tenant->plano_vence_em ? $tenant->plano_vence_em->format('d/m/Y') : 'Vitalício' }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Ação: Ativar/desativar tenant --}}
        <div class="adm-card mb-3">
            <div class="adm-card-title mb-2">Acesso ao Sistema</div>
            <form method="POST" action="{{ route('admin.tenants.toggle-ativo', $tenant) }}">
                @csrf
                @if($tenant->ativo)
                    <button type="submit" class="btn btn-sm btn-block"
                            style="border:1px solid rgba(229,62,62,.4);color:#fc8181;background:rgba(229,62,62,.08)"
                            onclick="return confirm('Desativar o acesso de {{ addslashes($tenant->nome) }}?')">
                        <i class="fas fa-ban mr-1"></i> Desativar Tenant
                    </button>
                @else
                    <button type="submit" class="btn btn-sm btn-block"
                            style="border:1px solid rgba(72,187,120,.4);color:#48bb78;background:rgba(72,187,120,.08)">
                        <i class="fas fa-check mr-1"></i> Reativar Tenant
                    </button>
                @endif
            </form>
        </div>

        {{-- Ação: Estender trial --}}
        <div class="adm-card mb-3">
            <div class="adm-card-title mb-2">Estender Trial</div>
            <form method="POST" action="{{ route('admin.tenants.extender-trial', $tenant) }}">
                @csrf
                <div class="d-flex" style="gap:.5rem">
                    <select name="dias" class="form-control form-control-sm"
                            style="background:#1a1d27;border-color:var(--adm-border);color:var(--adm-text)">
                        <option value="7">+7 dias</option>
                        <option value="15">+15 dias</option>
                        <option value="30" selected>+30 dias</option>
                        <option value="60">+60 dias</option>
                        <option value="90">+90 dias</option>
                    </select>
                    <button type="submit" class="btn btn-sm" style="background:#f6c90e;color:#000;border:none;white-space:nowrap">
                        <i class="fas fa-plus mr-1"></i> Aplicar
                    </button>
                </div>
            </form>
        </div>

        {{-- Ação: Plano pago --}}
        <div class="adm-card mb-3">
            <div class="adm-card-title mb-2">Plano Pago</div>
            @if($tenant->plano_ativo)
                <form method="POST" action="{{ route('admin.tenants.toggle-plano', $tenant) }}">
                    @csrf
                    <input type="hidden" name="ativar" value="0">
                    <button type="submit" class="btn btn-sm btn-block"
                            style="border:1px solid rgba(229,62,62,.4);color:#fc8181;background:rgba(229,62,62,.08)"
                            onclick="return confirm('Desativar plano pago de {{ addslashes($tenant->nome) }}?')">
                        <i class="fas fa-times mr-1"></i> Cancelar Plano
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.tenants.toggle-plano', $tenant) }}">
                    @csrf
                    <input type="hidden" name="ativar" value="1">
                    <div class="form-group mb-2">
                        <label style="font-size:.72rem;color:var(--adm-muted)">Vence em (opcional)</label>
                        <input type="date" name="vence_em"
                               class="form-control form-control-sm"
                               style="background:#1a1d27;border-color:var(--adm-border);color:var(--adm-text)"
                               value="{{ now()->addMonth()->toDateString() }}">
                    </div>
                    <button type="submit" class="btn btn-sm btn-block"
                            style="background:#48bb78;color:#000;border:none">
                        <i class="fas fa-check mr-1"></i> Ativar Plano
                    </button>
                </form>
            @endif
        </div>

    </div>

    {{-- Coluna direita: stats + usuários --}}
    <div class="col-md-8">

        {{-- Stats --}}
        <div class="row mb-3">
            <div class="col-4 mb-2">
                <div class="adm-card text-center">
                    <div class="adm-card-title">Usuários</div>
                    <div class="adm-stat" style="font-size:1.4rem">{{ $totalUsuarios }}</div>
                </div>
            </div>
            <div class="col-4 mb-2">
                <div class="adm-card text-center">
                    <div class="adm-card-title">Clientes</div>
                    <div class="adm-stat" style="font-size:1.4rem">{{ $totalClientes }}</div>
                </div>
            </div>
            <div class="col-4 mb-2">
                <div class="adm-card text-center">
                    <div class="adm-card-title">Veículos</div>
                    <div class="adm-stat" style="font-size:1.4rem">{{ $totalVeiculos }}</div>
                </div>
            </div>
            <div class="col-4 mb-2">
                <div class="adm-card text-center">
                    <div class="adm-card-title">Orçamentos</div>
                    <div class="adm-stat" style="font-size:1.4rem">{{ $totalOrcamentos }}</div>
                </div>
            </div>
            <div class="col-4 mb-2">
                <div class="adm-card text-center">
                    <div class="adm-card-title">O.S.</div>
                    <div class="adm-stat" style="font-size:1.4rem">{{ $totalOS }}</div>
                </div>
            </div>
        </div>

        {{-- Usuários do tenant --}}
        <div class="adm-card">
            <div class="adm-card-title mb-3">Usuários</div>
            @if($usuarios->isEmpty())
                <p style="color:var(--adm-muted);font-size:.82rem">Nenhum usuário cadastrado.</p>
            @else
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Usuário</th>
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Ativo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $u)
                        <tr>
                            <td style="font-weight:600">{{ $u->name }}</td>
                            <td style="font-family:monospace;color:var(--adm-muted)">{{ $u->username }}</td>
                            <td style="color:var(--adm-muted)">{{ $u->email }}</td>
                            <td>
                                <span style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em">
                                    {{ $u->perfil }}
                                </span>
                            </td>
                            <td>
                                @if($u->ativo)
                                    <span class="badge-pago">Sim</span>
                                @else
                                    <span class="badge-inativo">Não</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>
</div>

@endsection
