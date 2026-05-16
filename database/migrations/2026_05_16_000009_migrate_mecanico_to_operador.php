<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('perfil', 'mecanico')->update(['perfil' => 'operador']);
    }

    public function down(): void
    {
        // Irreversível por design — não há como saber quais eram mecanico vs operador original
    }
};
