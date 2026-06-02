<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sua conta no PitStop está ativa!</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:10px;overflow:hidden;">

                    {{-- Header --}}
                    <tr>
                        <td style="background:#111827;padding:24px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="color:#ffffff;font-size:20px;font-weight:700;">PitStop</td>
                                    <td style="text-align:right;">
                                        <span style="background:#16a34a;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;">
                                            ✓ Conta Ativa
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Corpo --}}
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 8px;font-size:18px;font-weight:700;color:#111827;">
                                Bem-vindo(a) ao PitStop, {{ $nomeUsuario }}!
                            </p>
                            <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#374151;">
                                A oficina <strong>{{ $nomeOficina }}</strong> está pronta para usar.
                                Seu trial gratuito de <strong>{{ $diasTrial }} dias</strong> começa agora.
                            </p>

                            {{-- Dados de acesso --}}
                            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
                                <p style="margin:0 0 10px;font-size:12px;font-weight:700;color:#15803d;text-transform:uppercase;letter-spacing:0.05em;">
                                    Seus dados de acesso
                                </p>
                                <table cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="font-size:13px;color:#6b7280;padding-bottom:6px;width:80px;">Endereço</td>
                                        <td style="font-size:13px;font-weight:600;color:#111827;padding-bottom:6px;">
                                            app.iaqueatende.com.br
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:13px;color:#6b7280;padding-bottom:6px;">Login</td>
                                        <td style="font-size:13px;font-weight:600;color:#111827;padding-bottom:6px;">
                                            {{ $login }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:13px;color:#6b7280;">Trial até</td>
                                        <td style="font-size:13px;font-weight:600;color:#111827;">
                                            {{ $trialFim?->format('d/m/Y') ?? '—' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            {{-- CTA --}}
                            <p style="margin:0 0 24px;text-align:center;">
                                <a href="{{ $loginUrl }}"
                                   style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:13px 32px;border-radius:8px;font-size:15px;font-weight:600;">
                                    Acessar o PitStop
                                </a>
                            </p>

                            {{-- O que fazer primeiro --}}
                            <p style="margin:0 0 12px;font-size:13px;font-weight:700;color:#111827;">O que fazer primeiro:</p>
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="vertical-align:top;padding-bottom:10px;width:22px;font-size:16px;">1️⃣</td>
                                    <td style="vertical-align:top;padding-bottom:10px;font-size:13px;color:#374151;line-height:1.5;">
                                        <strong>Conclua o onboarding</strong> — configure logo, horários e o primeiro funcionário
                                    </td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top;padding-bottom:10px;font-size:16px;">2️⃣</td>
                                    <td style="vertical-align:top;padding-bottom:10px;font-size:13px;color:#374151;line-height:1.5;">
                                        <strong>Cadastre seus clientes e veículos</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top;font-size:16px;">3️⃣</td>
                                    <td style="vertical-align:top;font-size:13px;color:#374151;line-height:1.5;">
                                        <strong>Abra o primeiro orçamento</strong> e veja o Kanban em ação
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:16px 32px;background:#f9fafb;font-size:11px;color:#9ca3af;text-align:center;border-top:1px solid #f3f4f6;">
                            PitStop — Gestão para oficinas mecânicas<br>
                            Dúvidas? Responda este e-mail ou acesse nossa <a href="{{ $loginUrl }}" style="color:#2563eb;text-decoration:none;">Central de Ajuda</a>.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
