<?php

namespace App\Http\Controllers\Concerns;

use App\Services\TrialLimitService;

trait ChecksTrialLimits
{
    protected function verificarLimiteTrial(string $recurso): ?(\Illuminate\Http\RedirectResponse)
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
