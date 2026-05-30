<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'username',
        'email',
        'password',
        'perfil',
        'ativo',
        'tentativas_login',
        'bloqueado_ate',
    ];

    protected $hidden = ['password', 'remember_token', 'session_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'ativo'             => 'boolean',
            'bloqueado_ate'     => 'datetime',
        ];
    }

    public function estaBloqueado(): bool
    {
        return $this->bloqueado_ate !== null && $this->bloqueado_ate->isFuture();
    }

    public function registrarTentativaFalha(): void
    {
        $tentativas = $this->tentativas_login + 1;

        if ($tentativas >= 3) {
            $this->update([
                'tentativas_login' => $tentativas,
                'bloqueado_ate'    => now()->addMinutes(30),
            ]);
        } else {
            $this->update(['tentativas_login' => $tentativas]);
        }
    }

    public function resetarBloqueio(): void
    {
        $this->update([
            'tentativas_login' => 0,
            'bloqueado_ate'    => null,
        ]);
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(UserLoginLog::class)->orderByDesc('logged_in_at');
    }

    public function isSuperAdmin(): bool { return $this->perfil === 'super_admin'; }
    public function isAdmin(): bool      { return $this->perfil === 'admin'; }
    public function isGerente(): bool    { return in_array($this->perfil, ['admin', 'gerente']); }
    public function isOperador(): bool   { return $this->perfil === 'operador'; }
}
