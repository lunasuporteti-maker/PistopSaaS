@extends('layouts.admin')
@section('title', 'Clientes')
@section('page_title', 'Clientes da Plataforma')

@section('content')

{{-- Filtros --}}
<div class="adm-card mb-4">
    <form method="GET" class="d-flex align-items-center" style="gap:.75rem;flex-wrap:wrap">
        <input type="text" name="search" class="form-control form-control-sm"
               style="background:#1a1d27;border-color:var(--adm-border);color:var(--adm-text);max-width:250px"
               placeholder="Buscar nome ou slug..." value="{{ request('search') }}">

        <select name="status" class="form-control form-control-sm"
                style="background:#1a1d27;border-color:var(--adm-border);color:var(--adm-text);max-width:160px">
            <option value="">Todos os status</option>
            <option value="trial"    {{ request('status') === 'trial'    ? 'selected' : '' }}>Em Trial</option>
            <option value="pago"     {{ request('status') === 'pago'     ? 'selected' : '' }}>Plano Pago</option>
            <option value="expirado" {{ request('status') === 'expirado' ? 'selected' : '' }}>Expirados</option>
            <option value="legado"   {{ request('status') === 'legado'   ? 'selected' : '' }}>Legados</option>
        </select>

        <button type="submit" class="btn btn-sm" style="background:#e53e3e;color:#fff;border:none">Buscar</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.tenants.index') }}" class="btn btn-sm" style="border:1px solid var(--adm-border);color:var(--adm-muted);background:none">Limpar</a>
        @endif
    </form>
</div>

{{-- Lista --}}
<div class="adm-card">
    <div style="overflow-x:auto">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Slug</th>
                    <th>Plano</th>
                    <th>Status</th>
                    <th>Trial até</th>
                    <th>Plano vence</th>
                    <th>Criado em</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                <tr>
                    <td>
                        <div style="font-weight:600">{{ $tenant->nome }}</div>
                        @if($tenant->dominio_customizado)
                            <div style="font-size:.7rem;color:var(--adm-muted)">{{ $tenant->dominio_customizado }}</div>
                        @endif
                    </td>
                    <td style="font-family:monospace;color:var(--adm-muted)">{{ $tenant->slug }}</td>
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
                    <td style="color:var(--adm-muted)">
                        {{ $tenant->trial_ends_at ? $tenant->trial_ends_at->format('d/m/Y') : '—' }}
                    </td>
                    <td style="color:var(--adm-muted)">
                        {{ $tenant->plano_vence_em ? $tenant->plano_vence_em->format('d/m/Y') : '—' }}
                    </td>
                    <td style="color:var(--adm-muted)">{{ $tenant->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.tenants.show', $tenant) }}"
                           style="color:#fc8181;font-size:.75rem;white-space:nowrap">
                            Detalhes →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:var(--adm-muted);padding:2.5rem">
                        Nenhum cliente encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tenants->hasPages())
    <div class="mt-3" style="font-size:.8rem">
        {{ $tenants->links() }}
    </div>
    @endif
</div>

@endsection
