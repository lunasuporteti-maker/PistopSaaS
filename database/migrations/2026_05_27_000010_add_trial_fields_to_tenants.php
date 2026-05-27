<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Data de expiração do trial (null = sem trial/sem restrição para tenants antigos)
            $table->timestamp('trial_ends_at')->nullable()->after('plano');
            // Plano pago ativo (true após confirmação de pagamento via webhook Asaas)
            $table->boolean('plano_ativo')->default(false)->after('trial_ends_at');
            // Data de vencimento do plano pago (null = plano não ativo)
            $table->date('plano_vence_em')->nullable()->after('plano_ativo');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['trial_ends_at', 'plano_ativo', 'plano_vence_em']);
        });
    }
};
