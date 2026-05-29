<?php

namespace App\Http\Controllers\Concerns;

use App\Services\TrialLimitService;
use Illuminate\Http\RedirectResponse;

trait ChecksTrialLimits
{
    protected function verificarLimiteTrial(string $recurso): ?RedirectResponse
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        if (! $tenant) {
            return null;
        }

        $service = app(TrialLimitService::class);

        if ($service->atingiuLimite($recurso, $tenant)) {
            return redirect()->back()->with('error', $service->mensagemLimite($recurso));
        }

        return null;
    }
}
