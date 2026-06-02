<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagamento confirmado — PitStop</title>
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
                                        <span style="background:#2563eb;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;">
                                            ✓ Pagamento Confirmado
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
                                Obrigado, {{ $nomeUsuario }}!
                            </p>
                            <p style="margin:0 0 24px;font-size:14px;line-height:1.6;color:#374151;">
                                Recebemos seu pagamento e a assinatura da oficina
                                <strong>{{ $nomeOficina }}</strong> está ativa.
                            </p>

                            {{-- Recibo --}}
                            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
                                <p style="margin:0 0 12px;font-size:12px;font-weight:700;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.05em;">
                                    Resumo do pagamento
                                </p>
                                <table cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="font-size:13px;color:#6b7280;padding-bottom:8px;width:130px;">Plano</td>
                                        <td style="font-size:13px;font-weight:600;color:#111827;padding-bottom:8px;">
                                            {{ $nomePlano }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:13px;color:#6b7280;padding-bottom:8px;">Valor pago</td>
                                        <td style="font-size:13px;font-weight:700;color:#16a34a;padding-bottom:8px;">
                                            R$ {{ number_format($valor, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:13px;color:#6b7280;">Próximo vencimento</td>
                                        <td style="font-size:13px;font-weight:600;color:#111827;">
                                            {{ \Carbon\Carbon::parse($proximoVencimento)->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            {{-- CTA --}}
                            <p style="margin:0 0 16px;text-align:center;">
                                <a href="{{ $loginUrl }}"
                                   style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:13px 32px;border-radius:8px;font-size:15px;font-weight:600;">
                                    Acessar o PitStop
                                </a>
                            </p>
                            <p style="margin:0 0 0;text-align:center;">
                                <a href="{{ $assinaturaUrl }}"
                                   style="font-size:12px;color:#6b7280;text-decoration:none;">
                                    Ver detalhes da assinatura
                                </a>
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:16px 32px;background:#f9fafb;font-size:11px;color:#9ca3af;text-align:center;border-top:1px solid #f3f4f6;">
                            PitStop — Gestão para oficinas mecânicas<br>
                            Guarde este e-mail como comprovante de pagamento.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
