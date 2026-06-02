<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona tenant_id apenas se ainda não existir (idempotente)
        if (!Schema::hasColumn('comissoes', 'tenant_id')) {
            Schema::table('comissoes', function (Blueprint $table) {
                // Nullable primeiro para não quebrar linhas existentes
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });

            // Limpa linhas sem tenant (dados órfãos da era sem multi-tenancy)
            \DB::table('comissoes')->whereNull('tenant_id')->delete();

            // Agora é seguro tornar NOT NULL
            Schema::table('comissoes', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            });
        }

        // os_id precisa ser nullable (formulário permite comissão sem OS)
        if (Schema::hasColumn('comissoes', 'os_id')) {
            Schema::table('comissoes', function (Blueprint $table) {
                $table->unsignedBigInteger('os_id')->nullable()->change();
            });
        }
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
