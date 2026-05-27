<aside class="sidebar drawer" id="mainSidebar">

    {{-- ── Logo ──────────────────────────────────────────────── --}}
    <div class="sb-brand">
        <a href="{{ route('dashboard') }}" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
            {{-- PitStop mark: cronômetro com cunha laranja --}}
            <svg width="22" height="22" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                <circle cx="16" cy="16" r="13" stroke="#fff" stroke-width="2"/>
                <rect x="15" y="1" width="2" height="4" rx="0.5" fill="#fff"/>
                <path d="M16 16 L16 5 A 11 11 0 0 1 27 16 Z" fill="var(--brand-500)"/>
                <circle cx="16" cy="16" r="2" fill="#fff"/>
            </svg>
            <span class="wordmark" style="font-weight:700;font-size:15px;letter-spacing:-0.02em;color:#fff;line-height:1;">
                pit<span style="color:var(--brand-400)">stop</span>
            </span>
        </a>
    </div>

    {{-- ── Navegação ───────────────────────────────────────────── --}}
    <nav class="sb-nav">

        {{-- Seção: Operação --}}
        <div class="sb-section">Operação</div>

        <a href="{{ route('dashboard') }}"
           class="sb-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="ic"><x-icon name="dashboard" size="16" /></span>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('kanban') }}" target="_blank"
           class="sb-link {{ request()->routeIs('kanban') ? 'active' : '' }}">
            <span class="ic"><x-icon name="kanban" size="16" /></span>
            <span>Kanban</span>
        </a>

        <a href="{{ route('fila') }}"
           class="sb-link {{ request()->routeIs('fila') ? 'active' : '' }}">
            <span class="ic"><x-icon name="filter" size="16" /></span>
            <span>Fila de Serviço</span>
        </a>

        <a href="{{ route('agendamentos.index') }}"
           class="sb-link {{ request()->routeIs('agendamentos.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="calendar" size="16" /></span>
            <span>Agendamentos</span>
        </a>

        @can('acima_de_mecanico')
        <a href="{{ route('orcamentos.index') }}"
           class="sb-link {{ request()->routeIs('orcamentos.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="receipt" size="16" /></span>
            <span>Orçamentos</span>
        </a>

        <a href="{{ route('ordens.index') }}"
           class="sb-link {{ request()->routeIs('ordens.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="wrench" size="16" /></span>
            <span>Ordens de Serviço</span>
        </a>
        @endcan

        {{-- Seção: Cadastros --}}
        @can('acima_de_mecanico')
        <div class="sb-section">Cadastros</div>

        <a href="{{ route('clientes.index') }}"
           class="sb-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="users" size="16" /></span>
            <span>Clientes</span>
        </a>

        <a href="{{ route('veiculos.index') }}"
           class="sb-link {{ request()->routeIs('veiculos.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="car" size="16" /></span>
            <span>Veículos</span>
        </a>

        <a href="{{ route('pecas.index') }}"
           class="sb-link {{ request()->routeIs('pecas.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="box" size="16" /></span>
            <span>Peças & Estoque</span>
        </a>

        <a href="{{ route('mao-de-obra.index') }}"
           class="sb-link {{ request()->routeIs('mao-de-obra.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="wrench" size="16" /></span>
            <span>Mão de Obra</span>
        </a>

        <a href="{{ route('catalogo-servicos.index') }}"
           class="sb-link {{ request()->routeIs('catalogo-servicos.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="layers" size="16" /></span>
            <span>Catálogo de Serviços</span>
        </a>

        <a href="{{ route('funcionarios.index') }}"
           class="sb-link {{ request()->routeIs('funcionarios.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="user" size="16" /></span>
            <span>Funcionários</span>
        </a>

        <a href="{{ route('parceiros.index') }}"
           class="sb-link {{ request()->routeIs('parceiros.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="users" size="16" /></span>
            <span>Parceiros</span>
        </a>
        @endcan

        {{-- Seção: Financeiro --}}
        @can('acima_de_mecanico')
        <div class="sb-section">Financeiro</div>

        <a href="{{ route('caixa.index') }}"
           class="sb-link {{ request()->routeIs('caixa.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="cash" size="16" /></span>
            <span>Caixa</span>
        </a>

        <a href="{{ route('financeiro.index') }}"
           class="sb-link {{ request()->routeIs('financeiro.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="receipt" size="16" /></span>
            <span>Lançamentos</span>
        </a>

        <a href="{{ route('lembretes.index') }}"
           class="sb-link {{ request()->routeIs('lembretes.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="bell" size="16" /></span>
            <span>Lembretes</span>
        </a>

        <a href="{{ route('relatorios.financeiro') }}"
           class="sb-link {{ request()->routeIs('relatorios.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="chart" size="16" /></span>
            <span>Relatórios</span>
        </a>
        @endcan

        {{-- Seção: Sistema --}}
        <div class="sb-section">Sistema</div>

        <a href="{{ route('perfil.edit') }}"
           class="sb-link {{ request()->routeIs('perfil.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="user" size="16" /></span>
            <span>Meu Perfil</span>
        </a>

        @can('acima_de_mecanico')
        <a href="{{ route('usuarios.index') }}"
           class="sb-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="users" size="16" /></span>
            <span>Usuários</span>
        </a>

        <a href="{{ route('configuracoes.index') }}"
           class="sb-link {{ request()->routeIs('configuracoes.*') ? 'active' : '' }}">
            <span class="ic"><x-icon name="settings" size="16" /></span>
            <span>Configurações</span>
        </a>
        @endcan

    </nav>

    {{-- ── Rodapé: usuário logado ───────────────────────────────── --}}
    <div class="sb-foot">
        <x-avatar :name="auth()->user()->name ?? 'User'" size="sm" />
        <div style="flex:1;min-width:0;overflow:hidden;">
            <div style="font-size:12px;font-weight:600;color:var(--sb-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ auth()->user()->name ?? '' }}
            </div>
            <div style="font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ auth()->user()->email ?? '' }}
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost btn-icon"
                    style="color:var(--sb-text-muted);" title="Sair">
                <x-icon name="arrowRight" size="14" />
            </button>
        </form>
    </div>

</aside>
