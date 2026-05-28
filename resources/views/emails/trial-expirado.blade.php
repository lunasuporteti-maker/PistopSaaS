<!DOCTYPE html>
<html lang="pt-BR">
<body style="font-family:sans-serif;max-width:560px;margin:0 auto;padding:24px;color:#1a1a1a;">
    <h2 style="color:#dc2626;">🔒 Seu trial expirou</h2>
    <p>Olá, <strong>{{ $tenant->nome }}</strong>!</p>
    <p>Seu período de teste gratuito do <strong>PitStop</strong> chegou ao fim.</p>
    <p>Seus dados estão seguros. Assine para voltar a usar todas as funcionalidades.</p>
    <p style="margin:24px 0;">
        <a href="{{ url('/assine') }}"
           style="background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;">
            Reativar minha conta →
        </a>
    </p>
    <p style="color:#666;font-size:13px;">Dúvidas? Responda este email.</p>
</body>
</html>
