@php
    $tenant = app()->bound(\App\Models\Tenant::class) ? app(\App\Models\Tenant::class) : null;
    $diasRestantes = $tenant?->diasRestantesTrial();
    $trialExpirado = session('trial_expirado', false);
    $alertaTrial = $tenant && $tenant->trial_ends_at !== null && !$tenant->emDia()
        && ($trialExpirado || ($diasRestantes !== null && $diasRestantes <= 5 && $diasRestantes >= 0));
@endphp

@if($trialExpirado)
<div style="background:#dc2626;color:#fff;text-align:center;padding:6px 12px;font-size:13px;">
    Seu trial expirou. <strong>Modo somente leitura ativo.</strong>
    <a href="{{ route('assine') }}" style="color:#fff;text-decoration:underline;margin-left:8px;">Assinar agora →</a>
</div>
@elseif($alertaTrial)
<div style="background:#d97706;color:#fff;text-align:center;padding:6px 12px;font-size:13px;">
    @if($diasRestantes === 0)
        Seu trial expira <strong>hoje</strong>!
    @else
        Seu trial expira em <strong>{{ $diasRestantes }} {{ $diasRestantes === 1 ? 'dia' : 'dias' }}</strong>.
    @endif
    <a href="{{ route('assine') }}" style="color:#fff;text-decoration:underline;margin-left:8px;">Ver planos →</a>
</div>
@endif

<header class="topbar">

    {{-- ── Botão de menu (mobile) ───────────────────────────── --}}
    <button class="btn btn-ghost btn-icon" onclick="openDrawer()"
            style="display:none;" id="menuToggle"
            aria-label="Abrir menu">
        <x-icon name="menu" size="18" />
    </button>

    {{-- ── Breadcrumbs / título da página ─────────────────────── --}}
    <div class="crumbs">
        <span>PitStop</span>
        <span class="sep">/</span>
        <span class="current">@yield('title', 'Dashboard')</span>
    </div>

    {{-- ── Busca ───────────────────────────────────────────────── --}}
    <div class="search-box">
        <span class="ic"><x-icon name="search" size="14" /></span>
        <input type="search" placeholder="Buscar…" aria-label="Buscar">
    </div>

    {{-- ── Ações à direita ─────────────────────────────────────── --}}
    <div class="row-flex" style="margin-left:auto;gap:4px;">

        {{-- Nova OS (atalho rápido) --}}
        @can('acima_de_mecanico')
        <a href="{{ route('orcamentos.create') }}" class="btn btn-primary btn-sm">
            <x-icon name="plus" size="13" />
            Nova OS
        </a>
        @endcan

        {{-- Toggle dark/light --}}
        <button id="themeToggle"
                class="btn btn-ghost btn-icon"
                onclick="toggleTheme()"
                title="Alternar tema"
                aria-label="Alternar tema claro/escuro">
            {{-- Sol = tema claro ativo --}}
            <span data-show-on="light">
                <x-icon name="moon" size="16" />
            </span>
            {{-- Lua = tema escuro ativo --}}
            <span data-show-on="dark" style="display:none;">
                <x-icon name="sun" size="16" />
            </span>
        </button>

    </div>

</header>

{{-- Mostra o botão de menu apenas no mobile --}}
<style>
@media (max-width: 768px) {
    #menuToggle { display: flex !important; }
}
</style>

{{-- Atualiza ícone do tema ao carregar --}}
<script>
(function () {
    var theme = document.documentElement.dataset.theme || 'light';
    var showLight = document.querySelectorAll('[data-show-on="light"]');
    var showDark  = document.querySelectorAll('[data-show-on="dark"]');
    if (theme === 'dark') {
        showLight.forEach(function(el){ el.style.display = 'none'; });
        showDark.forEach(function(el){ el.style.display = ''; });
    }

    // Mantém ícone sincronizado ao togglear
    var _orig = window.toggleTheme;
    window.toggleTheme = function () {
        _orig();
        var t = document.documentElement.dataset.theme;
        showLight.forEach(function(el){ el.style.display = t === 'dark' ? 'none' : ''; });
        showDark.forEach(function(el){ el.style.display = t === 'dark' ? '' : 'none'; });
    };
}());
</script>
