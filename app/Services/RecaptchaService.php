<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verificação de reCAPTCHA v3 (PRD 03, AC10).
 *
 * Em modo dev (chaves não configuradas no .env) o serviço aprova
 * automaticamente, permitindo desenvolvimento e testes sem rede.
 */
class RecaptchaService
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Indica se o reCAPTCHA está habilitado (chaves presentes).
     */
    public function habilitado(): bool
    {
        return ! empty(config('pitstop.recaptcha.secret_key'))
            && ! empty(config('pitstop.recaptcha.site_key'));
    }

    /**
     * Verifica o token. Retorna true quando aprovado OU quando o reCAPTCHA
     * está desabilitado (modo dev — bypass).
     */
    public function verificar(?string $token, ?string $ip = null): bool
    {
        // Bypass em dev: sem chaves configuradas.
        if (! $this->habilitado()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $resposta = Http::asForm()
                ->timeout(5)
                ->post(self::VERIFY_URL, [
                    'secret' => config('pitstop.recaptcha.secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            if (! $resposta->successful()) {
                Log::warning('reCAPTCHA: resposta não-sucesso', ['status' => $resposta->status()]);

                return false;
            }

            $dados = $resposta->json();

            if (! ($dados['success'] ?? false)) {
                return false;
            }

            $score = (float) ($dados['score'] ?? 0);

            return $score >= (float) config('pitstop.recaptcha.min_score', 0.5);
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA: falha na verificação', ['erro' => $e->getMessage()]);

            // Falha de rede não deve bloquear o cadastro de um lead legítimo.
            return true;
        }
    }
}
