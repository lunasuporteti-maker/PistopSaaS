<!DOCTYPE html>
<html lang="pt-BR">
<body style="font-family:sans-serif;max-width:560px;margin:0 auto;padding:24px;color:#1a1a1a;">
    <h2 style="color:#d97706;">⏰ Seu trial termina em 3 dias</h2>
    <p>Olá, <strong>{{ $tenant->nome }}</strong>!</p>
    <p>Seu período de teste gratuito do <strong>PitStop</strong> expira em <strong>3 dias</strong>
       ({{ $tenant->trial_ends_at->format('d/m/Y') }}).</p>
    <p>Para continuar usando o sistema sem interrupção, assine agora por <strong>R$99,90/mês</strong>.</p>
    <p style="margin:24px 0;">
        <a href="{{ url('/assine') }}"
           style="background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;">
            Assinar o PitStop →
        </a>
    </p>
    <p style="color:#666;font-size:13px;">Dúvidas? Responda este email.</p>
</body>
</html>
