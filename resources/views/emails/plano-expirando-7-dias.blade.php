<!DOCTYPE html>
<html lang="pt-BR">
<body style="font-family:sans-serif;max-width:560px;margin:0 auto;padding:24px;color:#1a1a1a;">
    <h2 style="color:#c44800;">📅 Seu plano vence em 7 dias</h2>
    <p>Olá, <strong>{{ $tenant->nome }}</strong>!</p>
    <p>Sua assinatura do <strong>PitStop {{ $tenant->nomePlano() }}</strong> vence em <strong>7 dias</strong>
       ({{ $tenant->plano_vence_em->format('d/m/Y') }}).</p>
    <p>Renove agora para garantir acesso contínuo ao sistema da sua oficina.</p>
    <p style="margin:24px 0;">
        <a href="{{ url('/assinatura') }}"
           style="background:#c44800;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;">
            Ver minha assinatura →
        </a>
    </p>
    <p style="color:#666;font-size:13px;">Dúvidas? Responda este e-mail ou acesse <a href="{{ url('/') }}">iaqueatende.com.br</a>.</p>
</body>
</html>
