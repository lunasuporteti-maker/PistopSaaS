<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Slugs reservados
    |--------------------------------------------------------------------------
    |
    | Subdomínios que não podem ser usados como slug de tenant no onboarding
    | self-service (PRD 03, AC4). A lista também serve para o IdentifyTenant
    | reconhecer subdomínios de sistema (ex: app) que NÃO mapeiam um tenant.
    |
    */
    'slugs_reservados' => [
        'www',
        'app',
        'api',
        'admin',
        'mail',
        'ftp',
        'blog',
        'docs',
        'status',
        'support',
    ],

    /*
    |--------------------------------------------------------------------------
    | Onboarding / signup
    |--------------------------------------------------------------------------
    */
    'signup' => [
        // Horas de validade do token de confirmação de e-mail (AC7/AC8).
        'token_validade_horas' => 24,

        // Duração do trial em dias ao provisionar o tenant (PRD 03, Story 4.3 AC9).
        'trial_dias' => (int) env('PITSTOP_TRIAL_DIAS', 30),

        // Domínio público onde o app de onboarding roda (link do e-mail, AC8).
        'app_url' => env('PITSTOP_APP_URL', 'https://app.iaqueatende.com.br'),

        // Versão atual dos termos/políticas aceitos (TermsAcceptance).
        'versao_termos' => env('PITSTOP_VERSAO_TERMOS', '1.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notificações admin (Story 4.8)
    |--------------------------------------------------------------------------
    */
    'admin_notify_email'       => env('ADMIN_NOTIFY_EMAIL'),
    'admin_notify_webhook_url' => env('ADMIN_NOTIFY_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA v3 (AC10)
    |--------------------------------------------------------------------------
    |
    | Quando site_key/secret_key não estão configurados, o RecaptchaService
    | entra em modo dev e aprova automaticamente (bypass).
    |
    */
    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'min_score' => (float) env('RECAPTCHA_MIN_SCORE', 0.5),
    ],

];
