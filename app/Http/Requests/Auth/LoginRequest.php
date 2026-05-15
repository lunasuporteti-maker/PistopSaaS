<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Verifica bloqueio por tentativas na conta
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($this->string('email'))])->first();

        if ($user && $user->estaBloqueado()) {
            $minutos = (int) ceil(now()->diffInSeconds($user->bloqueado_ate) / 60);
            throw ValidationException::withMessages([
                'email' => "Conta bloqueada por excesso de tentativas. Tente novamente em {$minutos} minuto(s) ou contate um Gerente.",
            ]);
        }

        if (! Auth::attempt(['email' => $this->string('email'), 'password' => $this->string('password')], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), 1800); // 30 min

            // Registra tentativa falha na conta
            if ($user) {
                $user->registrarTentativaFalha();
            }

            throw ValidationException::withMessages([
                'email' => 'Credenciais inválidas. ' . ($user ? "Tentativa {$user->fresh()->tentativas_login}/3." : ''),
            ]);
        }

        // Login bem-sucedido: limpa contadores
        if ($user) {
            $user->resetarBloqueio();
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => 'Muitas tentativas. Aguarde ' . ceil($seconds / 60) . ' minuto(s).',
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
