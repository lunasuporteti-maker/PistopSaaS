<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TourController extends Controller
{
    public function concluir(): JsonResponse
    {
        Auth::user()->update(['onboarding_tour_completo' => true]);
        return response()->json(['ok' => true]);
    }

    public function resetar(): RedirectResponse
    {
        Auth::user()->update(['onboarding_tour_completo' => false]);
        return redirect()->route('dashboard')->with('success', 'Tour de onboarding reiniciado.');
    }
}
