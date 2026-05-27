<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PitStop') | PitStop</title>

    {{-- Geist font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Geist+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- FontAwesome 5 — compat para ícones nas views legadas --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    {{-- Bootstrap 4 — camada de compat para forms, modais e grid --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    {{-- Vite: tokens + Tailwind compilados (vem depois — sobrescreve Bootstrap onde necessário) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Extra CSS das views --}}
    @stack('css')

    {{-- Theme: aplica antes de renderizar para evitar flash --}}
    <script>
        (function () {
            var t = localStorage.getItem('pitstop_theme') || 'light';
            document.documentElement.dataset.theme = t;
        }());
    </script>
</head>
<body>

<div class="app">

    {{-- ── Sidebar ────────────────────────────────────────────── --}}
    @include('partials.pitstop-sidebar')

    {{-- ── Backdrop drawer (mobile) ──────────────────────────── --}}
    <div class="drawer-backdrop" id="drawerBackdrop" onclick="closeDrawer()"></div>

    {{-- ── Main: topbar + content ─────────────────────────────── --}}
    <div class="main">

        @include('partials.pitstop-topbar')

        <div class="content">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <x-icon name="check" size="14" />
                    <span>{{ session('success') }}</span>
                    <button class="alert-dismiss" onclick="this.parentElement.remove()" aria-label="Fechar">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" role="alert">
                    <x-icon name="alert" size="14" />
                    <span>{{ session('error') }}</span>
                    <button class="alert-dismiss" onclick="this.parentElement.remove()" aria-label="Fechar">&times;</button>
                </div>
            @endif
            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger" role="alert">
                    <x-icon name="alert" size="14" />
                    <ul style="margin:0; padding-left:14px;">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                    <button class="alert-dismiss" onclick="this.parentElement.remove()" aria-label="Fechar">&times;</button>
                </div>
            @endif

            {{-- Cabeçalho da página (opcional nas views) --}}
            @hasSection('content_header')
                <div class="page-header">
                    @yield('content_header')
                </div>
            @endif

            {{-- Conteúdo principal --}}
            @yield('content')

        </div>

        {{-- Bottom nav mobile --}}
        <nav class="bottom-nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <x-icon name="dashboard" size="20" />
                <span>Home</span>
            </a>
            <a href="{{ route('kanban') }}" class="{{ request()->routeIs('kanban') ? 'active' : '' }}">
                <x-icon name="kanban" size="20" />
                <span>Kanban</span>
            </a>
            <a href="{{ route('orcamentos.index') }}" class="{{ request()->routeIs('orcamentos.*') ? 'active' : '' }}">
                <x-icon name="receipt" size="20" />
                <span>Orç.</span>
            </a>
            <a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                <x-icon name="users" size="20" />
                <span>Clientes</span>
            </a>
            <a href="{{ route('caixa.index') }}" class="{{ request()->routeIs('caixa.*') ? 'active' : '' }}">
                <x-icon name="cash" size="20" />
                <span>Caixa</span>
            </a>
        </nav>

    </div>
</div>

{{-- ── Scripts ──────────────────────────────────────────────── --}}
<script>
// ── Tema dark/light ──────────────────────────────────────────
window.toggleTheme = function () {
    var next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem('pitstop_theme', next);
    // Atualiza ícone do botão
    var btn = document.getElementById('themeToggle');
    if (btn) {
        btn.title = next === 'dark' ? 'Mudar para claro' : 'Mudar para escuro';
    }
};

// ── Drawer mobile (só ativa no mobile via CSS) ───────────────
window.openDrawer = function () {
    var sb = document.getElementById('mainSidebar');
    var bd = document.getElementById('drawerBackdrop');
    if (sb) sb.classList.add('open');
    if (bd) bd.classList.add('show');
    document.body.style.overflow = 'hidden';
};
window.closeDrawer = function () {
    var sb = document.getElementById('mainSidebar');
    var bd = document.getElementById('drawerBackdrop');
    if (sb) sb.classList.remove('open');
    if (bd) bd.classList.remove('show');
    document.body.style.overflow = '';
};
// Fecha drawer ao clicar num link (mobile)
document.addEventListener('click', function (e) {
    var link = e.target.closest('.sidebar a');
    if (link && window.innerWidth <= 768) closeDrawer();
});
</script>

{{-- Bootstrap compat JS (jQuery + Bootstrap bundle) --}}
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

@stack('js')
@stack('scripts')

</body>
</html>
