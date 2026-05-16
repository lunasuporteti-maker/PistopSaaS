<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Arquiva orçamentos concluídos há mais de 48h — roda a cada hora
Schedule::command('pitstop:arquivar-concluidos')->hourly();
