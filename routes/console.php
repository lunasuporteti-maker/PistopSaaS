<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Arquiva orçamentos concluídos há mais de 48h — roda a cada hora
Schedule::command('pitstop:arquivar-concluidos')->hourly();

// Verifica expiração de trial e envia emails D-3, D-1, D0
Schedule::command('pitstop:check-trial-expiry')->dailyAt('08:00');

// Verifica vencimento de plano pago e envia emails D-7, D-3, D-1
Schedule::command('pitstop:check-subscription-expiry')->dailyAt('08:00');
