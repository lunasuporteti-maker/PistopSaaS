<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracao extends Model
{
    use BelongsToTenant;

    protected $table = 'configuracoes';

    protected $fillable = ['tenant_id', 'chave', 'valor', 'descricao'];

    public static function get(string $chave, string $default = ''): string
    {
        $tenantId = app('tenant')?->id ?? 0;
        return Cache::remember("config_{$tenantId}_{$chave}", 300, function () use ($chave, $default) {
            return static::where('chave', $chave)->value('valor') ?? $default;
        });
    }

    public static function getForTenant(int $tenantId, string $chave, string $default = ''): string
    {
        return Cache::remember("config_{$tenantId}_{$chave}", 300, function () use ($tenantId, $chave, $default) {
            return static::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->where('chave', $chave)
                ->value('valor') ?? $default;
        });
    }

    public static function set(string $chave, string $valor): void
    {
        static::updateOrCreate(['chave' => $chave], ['valor' => $valor]);
        $tenantId = app('tenant')?->id ?? 0;
        Cache::forget("config_{$tenantId}_{$chave}");
    }
}
