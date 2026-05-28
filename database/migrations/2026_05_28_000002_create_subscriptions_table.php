<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Assinatura por tenant (PRD 03, seção 8). 1 subscription por tenant (unique).
     *
     * Tenants legados (AutoFix, trial_ends_at = NULL) NÃO terão registro aqui —
     * a tabela pode ficar vazia para eles (CON-001). O middleware CheckSubscription
     * já trata ausência de assinatura / trial nulo como passe livre.
     * Enums implementados como string + constantes no Model (portabilidade SQLite/PG).
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // 1 assinatura por tenant
            $table->unsignedBigInteger('tenant_id');

            // Plano: trial | padrao
            $table->string('plano', 30)->default('trial');

            // Status: trial | active | past_due | canceled | expired
            $table->string('status', 30)->default('trial');

            $table->timestamp('trial_termina_em')->nullable();
            $table->date('proximo_vencimento')->nullable();

            // Gateway: asaas | manual
            $table->string('gateway', 30)->default('asaas');
            $table->string('gateway_subscription_id', 120)->nullable();
            $table->string('gateway_customer_id', 120)->nullable();

            $table->timestamps();

            // Índices (AC5) — unique garante 1 assinatura por tenant
            $table->unique('tenant_id');

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
