<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('username', 'administrador')
            ->update([
                'tentativas_login' => 0,
                'bloqueado_ate'    => null,
            ]);
    }

    public function down(): void {}
};
