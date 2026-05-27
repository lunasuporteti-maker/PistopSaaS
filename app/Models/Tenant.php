<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'slug', 'dominio_customizado',
        'plano', 'ativo', 'observacao',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    // Resolve tenant pelo subdomain ou domínio customizado
    public static function resolverPorHost(string $host): ?static
    {
        // Domínio customizado exato
        $tenant = static::where('dominio_customizado', $host)->where('ativo', true)->first();
        if ($tenant) {
            return $tenant;
        }

        // Extrai subdomínio: "pitstop.pitstop.com.br" → "pitstop"
        $partes = explode('.', $host);
        if (count($partes) >= 3) {
            $slug = $partes[0];
            return static::where('slug', $slug)->where('ativo', true)->first();
        }

        return null;
    }
}
