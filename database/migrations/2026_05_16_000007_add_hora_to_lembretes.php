<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembretes', function (Blueprint $table) {
            $table->time('hora_lembrete')->nullable()->after('data_lembrete');
        });
    }

    public function down(): void
    {
        Schema::table('lembretes', function (Blueprint $table) {
            $table->dropColumn('hora_lembrete');
        });
    }
};
