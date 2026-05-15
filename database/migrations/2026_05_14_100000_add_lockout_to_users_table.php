<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('tentativas_login')->default(0)->after('ativo');
            $table->dateTime('bloqueado_ate')->nullable()->after('tentativas_login');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tentativas_login', 'bloqueado_ate']);
        });
    }
};
