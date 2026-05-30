<!DOCTYPE html>
<html lang="pt-BR">
<body style="font-family:sans-serif;max-width:560px;margin:0 auto;padding:24px;color:#1a1a1a;">
    <h2 style="color:#e53e3e;">⚠️ Seu plano vence amanhã</h2>
    <p>Olá, <strong>{{ $tenant->nome }}</strong>!</p>
    <p>Sua assinatura do <strong>PitStop {{ $tenant->nomePlano() }}</strong> vence <strong>amanhã</strong>
       ({{ $tenant->plano_vence_em->format('d/m/Y') }}).</p>
    <p>Após o vencimento, você tem 6 dias de carência com acesso completo. No 7º dia o acesso a edições fica suspenso até a renovação.</p>
    <p style="margin:24px 0;">
        <a href="{{ url('/assinatura') }}"
           style="background:#e53e3e;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;">
            Renovar agora →
        </a>
    </p>
    <p style="color:#666;font-size:13px;">Precisa de ajuda? Responda este e-mail ou fale pelo WhatsApp.</p>
</body>
</html>
