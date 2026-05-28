<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log de eventos de assinatura (PRD 03, seção 8). Tabela insert-only (audit).
     *
     * Sem updated_at por design — apenas created_at com useCurrent().
     * payload_json como text para portabilidade SQLite/PG (sem jsonb nativo).
     */
    public function up(): void
    {
        Schema::create('subscription_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id');

            // Tipo de evento (ex: webhook_payment_confirmed, trial_started, canceled)
            $table->string('evento', 80);

            // Payload bruto do evento (JSON serializado como texto)
            $table->text('payload_json')->nullable();

            // Insert-only: apenas created_at, sem updated_at
            $table->timestamp('created_at')->useCurrent();

            // Índice (AC5)
            $table->index('tenant_id');
            $table->index(['tenant_id', 'evento']);

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_logs');
    }
};
