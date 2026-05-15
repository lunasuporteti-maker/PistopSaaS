<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = ['chave', 'valor', 'descricao'];

    public static function get(string $chave, string $default = ''): string
    {
        return Cache::remember("config_{$chave}", 300, function () use ($chave, $default) {
            return static::where('chave', $chave)->value('valor') ?? $default;
        });
    }

    public static function set(string $chave, string $valor): void
    {
        static::updateOrCreate(['chave' => $chave], ['valor' => $valor]);
        Cache::forget("config_{$chave}");
    }
}
