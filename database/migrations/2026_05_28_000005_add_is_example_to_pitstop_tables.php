<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['clientes', 'veiculos', 'pecas', 'funcionarios'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->boolean('is_example')->default(false)->after('tenant_id');
            });
        }
    }

    public function down(): void
    {
        foreach (['clientes', 'veiculos', 'pecas', 'funcionarios'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('is_example');
            });
        }
    }
};
