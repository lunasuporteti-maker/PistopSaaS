<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->text('andamento')->nullable()->after('parecer_tecnico');
        });
    }

    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->dropColumn('andamento');
        });
    }
};
