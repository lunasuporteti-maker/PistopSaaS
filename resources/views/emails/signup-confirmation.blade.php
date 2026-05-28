<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirme seu cadastro no PitStop</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:10px;overflow:hidden;">
                    <tr>
                        <td style="background:#111827;padding:24px 32px;color:#ffffff;font-size:20px;font-weight:700;">
                            PitStop
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:16px;">Olá, {{ $nome }}!</p>
                            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#374151;">
                                Recebemos o cadastro da oficina <strong>{{ $nomeOficina }}</strong> no PitStop.
                                Para ativar seu trial gratuito de 14 dias, confirme seu e-mail clicando no botão abaixo.
                            </p>
                            <p style="margin:24px 0;text-align:center;">
                                <a href="{{ $link }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:15px;font-weight:600;">
                                    Confirmar meu e-mail
                                </a>
                            </p>
                            <p style="margin:0 0 8px;font-size:13px;color:#6b7280;line-height:1.6;">
                                Se o botão não funcionar, copie e cole este link no navegador:
                            </p>
                            <p style="margin:0 0 16px;font-size:12px;color:#2563eb;word-break:break-all;">
                                {{ $link }}
                            </p>
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                Este link é válido até {{ $expiraEm?->format('d/m/Y H:i') }}. Se você não solicitou este cadastro, ignore este e-mail.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px;background:#f9fafb;font-size:11px;color:#9ca3af;text-align:center;">
                            PitStop — Gestão para oficinas mecânicas
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
