<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AsaasService;
use Illuminate\View\View;

class PlanoController extends Controller
{
    public function index(): View
    {
        $tenant      = app()->bound('tenant') ? app('tenant') : null;
        $asaasConfig = config('services.asaas.payment_link_url') || config('services.asaas.api_key');

        return view('pitstop.assine', compact('tenant', 'asaasConfig'));
    }

    public function checkout(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $tenant  = app('tenant');
        $user    = auth()->user();
        $service = app(AsaasService::class);

        // Aplica o tier escolhido pelo usuário antes de gerar o checkout
        $tier = $request->input('plano_tier', $tenant->tier());
        if (in_array($tier, ['pro', 'pro_max'])) {
            $tenant->plano_tier = $tier;
        }

        $url = $service->createCheckoutUrl($tenant, $user);

        if ($url) {
            return redirect()->away($url);
        }

        return back()->with('error', 'Link de pagamento não configurado. Entre em contato: iaqueatende@gmail.com');
    }
}
