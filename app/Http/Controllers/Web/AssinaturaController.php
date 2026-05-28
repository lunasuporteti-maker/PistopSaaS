<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionLog;

class AssinaturaController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        $logs   = SubscriptionLog::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('pitstop.assinatura', compact('tenant', 'logs'));
    }
}
