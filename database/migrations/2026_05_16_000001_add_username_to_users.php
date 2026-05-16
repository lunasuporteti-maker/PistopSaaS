<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona coluna username (nullable primeiro para popular os existentes)
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->after('name');
        });

        // Popula username a partir do name para usuários existentes (lowercase, sem espaços)
        DB::statement("UPDATE users SET username = LOWER(REPLACE(name, ' ', '_')) WHERE username IS NULL");

        // Torna obrigatório e único
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable(false)->unique()->change();

            // Torna email opcional
            $table->string('email', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
            $table->string('email', 120)->nullable(false)->change();
        });
    }
};
