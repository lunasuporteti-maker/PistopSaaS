<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — IAQueAtende</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        :root {
            --adm-bg:       #0f1117;
            --adm-surface:  #1a1d27;
            --adm-card:     #20243a;
            --adm-border:   #2e3347;
            --adm-text:     #e2e8f0;
            --adm-muted:    #8892a4;
            --adm-accent:   #e53e3e;
            --adm-accent2:  #fc8181;
            --adm-green:    #48bb78;
            --adm-yellow:   #f6c90e;
            --adm-sidebar:  180px;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--adm-bg);
            color: var(--adm-text);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .adm-sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--adm-sidebar);
            background: var(--adm-surface);
            border-right: 1px solid var(--adm-border);
            display: flex;
            flex-direction: column;
            z-index: 100;
            overflow-y: auto;
        }

        .adm-sidebar .brand {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--adm-border);
        }

        .adm-sidebar .brand .brand-name {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .05em;
            color: var(--adm-accent2);
            text-transform: uppercase;
        }

        .adm-sidebar .brand .brand-sub {
            font-size: .65rem;
            color: var(--adm-muted);
            margin-top: .1rem;
        }

        .adm-nav { padding: .75rem 0; flex: 1; }

        .adm-nav .nav-section {
            font-size: .6rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--adm-muted);
            padding: .5rem 1rem .25rem;
        }

        .adm-nav a {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .55rem 1rem;
            font-size: .8rem;
            color: var(--adm-muted);
            text-decoration: none;
            transition: all .15s;
            border-left: 3px solid transparent;
        }

        .adm-nav a:hover,
        .adm-nav a.active {
            color: var(--adm-text);
            background: rgba(229,62,62,.08);
            border-left-color: var(--adm-accent);
        }

        .adm-nav a i { width: 16px; text-align: center; }

        .adm-sidebar-footer {
            padding: .75rem 1rem;
            border-top: 1px solid var(--adm-border);
            font-size: .7rem;
            color: var(--adm-muted);
        }

        /* ── Main ── */
        .adm-main {
            margin-left: var(--adm-sidebar);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .adm-topbar {
            background: var(--adm-surface);
            border-bottom: 1px solid var(--adm-border);
            padding: .65rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .adm-topbar .page-title {
            font-size: .95rem;
            font-weight: 600;
            color: var(--adm-text);
        }

        .adm-topbar .user-info {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-size: .78rem;
            color: var(--adm-muted);
        }

        .adm-topbar .user-info .badge-admin {
            background: var(--adm-accent);
            color: #fff;
            font-size: .6rem;
            font-weight: 700;
            padding: .2rem .5rem;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .adm-content {
            padding: 1.5rem;
            flex: 1;
        }

        /* ── Cards ── */
        .adm-card {
            background: var(--adm-card);
            border: 1px solid var(--adm-border);
            border-radius: 10px;
            padding: 1.25rem;
        }

        .adm-card-title {
            font-size: .8rem;
            font-weight: 600;
            color: var(--adm-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .5rem;
        }

        .adm-stat {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--adm-text);
        }

        /* ── Tables ── */
        .adm-table { width: 100%; border-collapse: collapse; font-size: .82rem; }

        .adm-table th {
            background: var(--adm-bg);
            color: var(--adm-muted);
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .6rem .75rem;
            border-bottom: 1px solid var(--adm-border);
        }

        .adm-table td {
            padding: .65rem .75rem;
            border-bottom: 1px solid var(--adm-border);
            color: var(--adm-text);
            vertical-align: middle;
        }

        .adm-table tr:last-child td { border-bottom: none; }
        .adm-table tr:hover td { background: rgba(255,255,255,.02); }

        /* ── Badges de status ── */
        .badge-trial   { background: rgba(246,201,14,.15); color: #f6c90e; border: 1px solid rgba(246,201,14,.3); font-size: .68rem; padding: .2rem .55rem; border-radius: 4px; }
        .badge-pago    { background: rgba(72,187,120,.15); color: #48bb78; border: 1px solid rgba(72,187,120,.3); font-size: .68rem; padding: .2rem .55rem; border-radius: 4px; }
        .badge-expirado{ background: rgba(229,62,62,.15);  color: #fc8181; border: 1px solid rgba(229,62,62,.3);  font-size: .68rem; padding: .2rem .55rem; border-radius: 4px; }
        .badge-legado  { background: rgba(142,152,164,.15);color: #8892a4; border: 1px solid rgba(142,152,164,.3);font-size: .68rem; padding: .2rem .55rem; border-radius: 4px; }
        .badge-inativo { background: rgba(229,62,62,.1);   color: #fc8181; border: 1px solid rgba(229,62,62,.2);  font-size: .68rem; padding: .2rem .55rem; border-radius: 4px; }

        /* ── Alerts ── */
        .adm-alert-success { background: rgba(72,187,120,.12); border: 1px solid rgba(72,187,120,.3); color: #48bb78; border-radius: 8px; padding: .75rem 1rem; margin-bottom: 1rem; }
        .adm-alert-error   { background: rgba(229,62,62,.12);  border: 1px solid rgba(229,62,62,.3);  color: #fc8181; border-radius: 8px; padding: .75rem 1rem; margin-bottom: 1rem; }
    </style>

    @stack('css')
</head>
<body>

{{-- Sidebar --}}
<aside class="adm-sidebar">
    <div class="brand">
        <div class="brand-name">IAQueAtende</div>
        <div class="brand-sub">Painel Admin</div>
    </div>

    <nav class="adm-nav">
        <div class="nav-section">Plataforma</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="{{ route('admin.tenants.index') }}" class="{{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
            <i class="fas fa-building"></i> Clientes
        </a>

        <div class="nav-section" style="margin-top:.5rem">Conta</div>
        <a href="{{ route('admin.conta') }}" class="{{ request()->routeIs('admin.conta') ? 'active' : '' }}">
            <i class="fas fa-key"></i> Minha Senha
        </a>
        <a href="{{ route('dashboard') }}" target="_blank">
            <i class="fas fa-external-link-alt"></i> Ver PitStop
        </a>
    </nav>

    <div class="adm-sidebar-footer">
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" style="background:none;border:none;color:var(--adm-muted);cursor:pointer;font-size:.7rem;padding:0;width:100%;text-align:left">
                <i class="fas fa-sign-out-alt mr-1"></i> Sair
            </button>
        </form>
    </div>
</aside>

{{-- Main --}}
<main class="adm-main">
    <div class="adm-topbar">
        <div class="page-title">@yield('page_title', 'Admin')</div>
        <div class="user-info">
            <span class="badge-admin">Super Admin</span>
            <span>{{ Auth::user()->name }}</span>
        </div>
    </div>

    <div class="adm-content">
        @if(session('success'))
            <div class="adm-alert-success"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="adm-alert-error"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</main>

<!-- jQuery + Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

@stack('js')
</body>
</html>
