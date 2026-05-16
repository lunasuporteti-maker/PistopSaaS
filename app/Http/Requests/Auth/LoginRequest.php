<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $username = trim($this->string('username'));

        // Busca pelo campo username (case-insensitive, ignora tenant no login)
        $user = User::withoutGlobalScope('tenant')
            ->whereRaw('LOWER(username) = ?', [mb_strtolower($username)])
            ->first();

        if ($user && $user->estaBloqueado()) {
            $minutos = (int) ceil(now()->diffInSeconds($user->bloqueado_ate) / 60);
            throw ValidationException::withMessages([
                'username' => "Conta bloqueada por excesso de tentativas. Tente novamente em {$minutos} minuto(s) ou contate um Gerente.",
            ]);
        }

        if (! $user || ! Hash::check($this->string('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey(), 1800);

            if ($user) {
                $user->registrarTentativaFalha();
            }

            throw ValidationException::withMessages([
                'username' => 'Usuário ou senha incorretos.' . ($user ? " Tentativa {$user->fresh()->tentativas_login}/3." : ''),
            ]);
        }

        if (! $user->ativo) {
            throw ValidationException::withMessages([
                'username' => 'Esta conta está desativada. Contate o Administrador.',
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

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
            'username' => 'Muitas tentativas. Aguarde ' . ceil($seconds / 60) . ' minuto(s).',
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')) . '|' . $this->ip());
    }
}
