<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AutoFix — Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #1e1e3a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Decoração de fundo */
        body::before {
            content: '';
            position: fixed;
            top: -30%;
            right: -10%;
            width: 60vw;
            height: 60vw;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(192,57,43,.12) 0%, transparent 70%);
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -20%;
            left: -10%;
            width: 50vw;
            height: 50vw;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(21,101,192,.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .login-wrap {
            width: 100%;
            max-width: 400px;
            padding: 0 16px;
            position: relative;
            z-index: 1;
        }

        /* Marca */
        .login-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-brand .logo-img {
            max-width: 180px;
            max-height: 80px;
            object-fit: contain;
            margin-bottom: 12px;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,.4));
        }
        .login-brand p {
            color: rgba(255,255,255,.45);
            font-size: .82rem;
            font-weight: 400;
            margin-top: 4px;
        }

        /* Card */
        .login-card {
            background: rgba(255,255,255,.97);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 24px 60px rgba(0,0,0,.4);
        }

        .login-card h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
        }
        .login-card p {
            font-size: .82rem;
            color: #888;
            margin-bottom: 24px;
        }

        .form-group { margin-bottom: 16px; }
        .form-group label {
            font-size: .8rem;
            font-weight: 600;
            color: #4a5568;
            display: block;
            margin-bottom: 5px;
        }

        .input-wrap {
            position: relative;
        }
        .input-wrap .icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: .85rem;
        }
        .input-wrap input {
            width: 100%;
            padding: 10px 12px 10px 36px;
            border: 1.5px solid #dce1e7;
            border-radius: 9px;
            font-size: .875rem;
            font-family: 'Inter', sans-serif;
            transition: border-color .18s, box-shadow .18s;
            outline: none;
            color: #2d3436;
        }
        .input-wrap input:focus {
            border-color: #c0392b;
            box-shadow: 0 0 0 3px rgba(192,57,43,.1);
        }
        .input-wrap input.is-invalid { border-color: #e74c3c; }

        .invalid-feedback {
            font-size: .78rem;
            color: #e74c3c;
            margin-top: 4px;
            display: block;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            color: #fff;
            border: none;
            border-radius: 9px;
            font-size: .9rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all .18s ease;
            box-shadow: 0 4px 12px rgba(192,57,43,.3);
            margin-top: 8px;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(192,57,43,.4);
        }
        .btn-login:active { transform: translateY(0); }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            color: rgba(255,255,255,.3);
            font-size: .75rem;
        }
        .login-footer a { color: rgba(255,255,255,.5); text-decoration: none; }
        .login-footer a:hover { color: rgba(255,255,255,.8); }

        /* Alerta de erro */
        .alert-login {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: .82rem;
            color: #991b1b;
            margin-bottom: 16px;
        }
        .alert-login ul { margin: 0; padding: 0 0 0 16px; }
        .alert-login li { margin-top: 2px; }
    </style>
</head>
<body>
<div class="login-wrap">

    {{-- Marca --}}
    <div class="login-brand">
        <img src="/images/logo_autofix.png" alt="AutoFix" class="logo-img">
        <p>Sistema de Gestão para Oficinas Mecânicas</p>
    </div>

    {{-- Card de login --}}
    <div class="login-card">
        <h2>Bem-vindo de volta!</h2>
        <p>Informe suas credenciais de acesso.</p>

        {{-- Erros --}}
        @if ($errors->any())
        <div class="alert-login">
            @if ($errors->count() === 1)
                <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first() }}
            @else
                <i class="fas fa-exclamation-circle mr-1"></i>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            @endif
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">E-mail</label>
                <div class="input-wrap">
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="seu@email.com"
                           class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                           required autofocus autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <div class="input-wrap">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                           required autocomplete="current-password">
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt mr-2"></i>Entrar no Sistema
            </button>
        </form>
    </div>

    {{-- Footer --}}
    <div class="login-footer">
        <p>Desenvolvido por <a href="#">IAQueAtende</a></p>
        <p style="margin-top:4px">(81) 99811-4585</p>
    </div>

</div>
</body>
</html>
