<?php

namespace App\Listeners;

use App\Models\UserLoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class LogUserLogin
{
    public function handle(Login $event): void
    {
        try {
            $user = $event->user;

            UserLoginLog::create([
                'user_id'     => $user->getKey(),
                'logged_in_at' => now(),
                'ip_address'  => Request::ip(),
            ]);

            // Mantém somente os 3 registros mais recentes por usuário
            $keepIds = UserLoginLog::where('user_id', $user->getKey())
                ->orderByDesc('logged_in_at')
                ->limit(3)
                ->pluck('id');

            UserLoginLog::where('user_id', $user->getKey())
                ->whereNotIn('id', $keepIds)
                ->delete();
        } catch (\Throwable $e) {
            Log::error('Falha ao registrar login do usuário ' . ($event->user->getKey() ?? '?') . ': ' . $e->getMessage());
        }
    }
}
