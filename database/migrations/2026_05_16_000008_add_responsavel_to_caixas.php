<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caixas', function (Blueprint $table) {
            $table->foreignId('aberto_por_user_id')->nullable()->after('aberto_em')->constrained('users')->nullOnDelete();
            $table->foreignId('fechado_por_user_id')->nullable()->after('fechado_em')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('caixas', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'aberto_por_user_id');
            $table->dropForeignIdFor(\App\Models\User::class, 'fechado_por_user_id');
        });
    }
};
