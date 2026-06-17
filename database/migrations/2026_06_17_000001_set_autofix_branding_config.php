<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Configura a marca da AutoFix no PDF: cor primaria (laranja) e slogan.
     * Multi-tenant: so afeta o tenant 'autofix'. Outras oficinas usam o padrao.
     * Idempotente (updateOrInsert) — pode rodar de novo sem duplicar.
     */
    public function up(): void
    {
        $tenant = DB::table('tenants')->where('slug', 'autofix')->first();
        if (! $tenant) {
            return;
        }

        $configs = [
            'cor_primaria'   => '#F26522',                 // laranja AutoFix
            'slogan_oficina' => 'Confiança que move você.', // slogan do rodape
        ];

        foreach ($configs as $chave => $valor) {
            DB::table('configuracoes')->updateOrInsert(
                ['tenant_id' => $tenant->id, 'chave' => $chave],
                ['valor' => $valor, 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $tenant = DB::table('tenants')->where('slug', 'autofix')->first();
        if (! $tenant) {
            return;
        }

        DB::table('configuracoes')
            ->where('tenant_id', $tenant->id)
            ->whereIn('chave', ['cor_primaria', 'slogan_oficina'])
            ->delete();
    }
};
