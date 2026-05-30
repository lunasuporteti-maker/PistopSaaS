@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard da Plataforma')

@section('content')

{{-- KPIs --}}
<div class="row mb-4">
    <div class="col-md-2 col-sm-4 mb-3">
        <div class="adm-card h-100">
            <div class="adm-card-title">Total Clientes</div>
            <div class="adm-stat">{{ $totalTenants }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 mb-3">
        <div class="adm-card h-100">
            <div class="adm-card-title">Ativos</div>
            <div class="adm-stat" style="color:#48bb78">{{ $tenantsAtivos }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 mb-3">
        <div class="adm-card h-100">
            <div class="adm-card-title">Em Trial</div>
            <div class="adm-stat" style="color:#f6c90e">{{ $emTrial }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 mb-3">
        <div class="adm-card h-100">
            <div class="adm-card-title">Plano Pago</div>
            <div class="adm-stat" style="color:#48bb78">{{ $planoPago }}</div>
            @if($novosSemana > 0)
                <div style="font-size:.7rem;color:#68d391;margin-top:.25rem">+{{ $novosSemana }} esta semana</div>
            @endif
        </div>
    </div>
    <div class="col-md-2 col-sm-4 mb-3">
        <div class="adm-card h-100">
            <div class="adm-card-title">Expirados</div>
            <div class="adm-stat" style="color:#fc8181">{{ $expirados }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 mb-3">
        <div class="adm-card h-100">
            <div class="adm-card-title">Usuários</div>
            <div class="adm-stat">{{ $totalUsuarios }}</div>
        </div>
    </div>
</div>

{{-- Top 10 mais ativos --}}
@if($maisAtivos->isNotEmpty())
<div class="adm-card mb-4">
    <div class="adm-card-title mb-3">Mais ativos — últimos 30 dias</div>
    <table class="adm-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Plano</th>
                <th style="text-align:right">OS / 30d</th>
                <th>Usuários</th>
            </tr>
        </thead>
        <tbody>
            @foreach($maisAtivos as $i => $item)
            <tr>
                <td style="color:var(--adm-muted)">{{ $i + 1 }}</td>
                <td style="font-weight:600">
                    <a href="{{ route('admin.tenants.show', $item['tenant']) }}" style="color:var(--adm-text)">
                        {{ $item['tenant']->nome }}
                    </a>
                </td>
                <td><span style="font-size:.75rem">{{ $item['tenant']->nomePlano() }}</span></td>
                <td style="text-align:right;font-weight:700;color:#48bb78">{{ $item['os_30d'] }}</td>
                <td style="color:var(--adm-muted)">{{ $item['tenant']->users()->count() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Clientes recentes --}}
<div class="adm-card">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div style="font-size:.9rem;font-weight:600">Clientes Recentes</div>
        <a href="{{ route('admin.tenants.index') }}" style="font-size:.78rem;color:#fc8181">Ver todos →</a>
    </div>

    <div style="overflow-x:auto">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Slug</th>
                    <th>Plano</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentes as $tenant)
                <tr>
                    <td style="font-weight:600">{{ $tenant->nome }}</td>
                    <td style="color:var(--adm-muted);font-family:monospace">{{ $tenant->slug }}</td>
                    <td>{{ ucfirst($tenant->plano) }}</td>
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
                        @if(! $tenant->ativo)
                            <span class="badge-inativo ml-1">Inativo</span>
                        @endif
                    </td>
                    <td style="color:var(--adm-muted)">{{ $tenant->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.tenants.show', $tenant) }}" style="color:#fc8181;font-size:.75rem">
                            Ver →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:var(--adm-muted);padding:2rem">
                        Nenhum cliente cadastrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
