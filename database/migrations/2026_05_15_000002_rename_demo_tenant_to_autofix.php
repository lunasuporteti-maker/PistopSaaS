<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Renomeia o tenant demo para autofix (mudança de domínio do cliente)
        DB::table('tenants')
            ->where('slug', 'demo')
            ->update([
                'slug'       => 'autofix',
                'nome'       => 'AutoFix',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('tenants')
            ->where('slug', 'autofix')
            ->update([
                'slug'       => 'demo',
                'nome'       => 'PitStop Demo',
                'updated_at' => now(),
            ]);
    }
};
