<!DOCTYPE html>
<html lang="pt-BR">
<body style="font-family:sans-serif;max-width:560px;margin:0 auto;padding:24px;color:#1a1a1a;">
    <h2 style="color:#dc2626;">⚠️ Seu trial termina amanhã</h2>
    <p>Olá, <strong>{{ $tenant->nome }}</strong>!</p>
    <p>Seu período de teste gratuito do <strong>PitStop</strong> expira <strong>amanhã</strong>
       ({{ $tenant->trial_ends_at->format('d/m/Y') }}).</p>
    <p>Após o vencimento, seu acesso ficará em modo somente leitura até você assinar.</p>
    <p style="margin:24px 0;">
        <a href="{{ url('/assine') }}"
           style="background:#dc2626;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;">
            Assinar agora — R$99,90/mês →
        </a>
    </p>
    <p style="color:#666;font-size:13px;">Dúvidas? Responda este email.</p>
</body>
</html>
