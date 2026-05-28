<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conta criada | PitStop</title>

    {{-- Redireciona automaticamente para o onboarding do tenant (AC6). --}}
    @isset($redirectUrl)
        <meta http-equiv="refresh" content="2;url={{ $redirectUrl }}">
    @endisset

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body { font-family:'Geist',sans-serif; background:#f4f5f7; color:#1a1a1a; }
        .confirm-wrap { max-width:480px; margin:0 auto; padding:48px 16px; }
        .confirm-card { background:#fff; border-radius:14px; box-shadow:0 4px 24px rgba(0,0,0,.06); padding:40px 32px; text-align:center; }
        .icon-circle { width:72px; height:72px; border-radius:50%; background:#dcfce7; color:#16a34a; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:34px; }
    </style>
</head>
<body>
<div class="confirm-wrap">
    <div class="confirm-card">
        <div class="icon-circle">&#10003;</div>
        <h4 class="mb-2">Conta criada!</h4>
        <p class="text-muted">Sua oficina foi ativada com sucesso. Redirecionando para o painel...</p>

        @isset($redirectUrl)
            <p class="mt-4" style="font-size:.85rem;">
                Não foi redirecionado?
                <a href="{{ $redirectUrl }}">Clique aqui</a>.
            </p>
        @endisset
    </div>
</div>
</body>
</html>
