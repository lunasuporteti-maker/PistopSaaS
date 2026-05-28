<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titulo ?? 'Erro na confirmação' }} | PitStop</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body { font-family:'Geist',sans-serif; background:#f4f5f7; color:#1a1a1a; }
        .confirm-wrap { max-width:480px; margin:0 auto; padding:48px 16px; }
        .confirm-card { background:#fff; border-radius:14px; box-shadow:0 4px 24px rgba(0,0,0,.06); padding:40px 32px; text-align:center; }
        .icon-circle { width:72px; height:72px; border-radius:50%; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:34px; }
        .email-strong { color:#111827; font-weight:600; }
        .btn-pitstop { background:#2563eb; border:0; font-weight:600; }
        .btn-pitstop:hover { background:#1d4ed8; }
    </style>
</head>
<body>
<div class="confirm-wrap">
    <div class="confirm-card">
        <div class="icon-circle">&#9888;</div>
        <h4 class="mb-2">{{ $titulo ?? 'Não foi possível confirmar' }}</h4>
        <p class="text-muted">{{ $mensagem ?? 'Ocorreu um erro ao confirmar seu cadastro.' }}</p>

        @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-warning mt-3">{{ session('error') }}</div>
        @endif

        @if(! empty($podeReenviar))
            @if(! empty($emailMascarado))
                <p class="text-muted mt-3" style="font-size:.9rem;">
                    Enviaremos um novo link para <span class="email-strong">{{ $emailMascarado }}</span>.
                </p>
            @endif
            <form method="POST" action="{{ route('cadastro.reenviar-email') }}" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-pitstop text-white">Reenviar e-mail de confirmação</button>
            </form>
        @endif

        @if(! empty($mostrarSuporte))
            <p class="text-muted mt-4" style="font-size:.85rem;">
                Precisa de ajuda? Fale com o suporte:
                <a href="mailto:suporte@iaqueatende.com.br">suporte@iaqueatende.com.br</a>
            </p>
        @endif

        <p class="mt-4" style="font-size:.8rem;">
            <a href="{{ route('cadastro.form') }}" class="text-muted">Voltar ao cadastro</a>
        </p>
    </div>
</div>
</body>
</html>
