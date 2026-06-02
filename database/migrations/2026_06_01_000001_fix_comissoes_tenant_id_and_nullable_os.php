<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comissoes', function (Blueprint $table) {
            // Adiciona tenant_id — obrigatório para BelongsToTenant funcionar
            $table->unsignedBigInteger('tenant_id')->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            // os_id precisa ser nullable (formulário permite comissão sem OS)
            $table->unsignedBigInteger('os_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('comissoes', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
            $table->unsignedBigInteger('os_id')->nullable(false)->change();
        });
    }
};
