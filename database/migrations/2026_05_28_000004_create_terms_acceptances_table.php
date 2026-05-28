<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro de aceite de termos / consentimentos (PRD 03, seção 8). Insert-only.
     *
     * Todas as FKs são nullable — o aceite pode ocorrer na etapa de signup
     * (tenant_signup_id) antes do tenant/user existirem, ou depois (tenant_id/user_id).
     * Sem updated_at por design (registro imutável de consentimento — LGPD).
     */
    public function up(): void
    {
        Schema::create('terms_acceptances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_signup_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            // Tipo: termos_uso | privacidade | marketing (enum como string)
            $table->string('tipo', 30);
            $table->string('versao', 30);

            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Insert-only: apenas created_at
            $table->timestamp('created_at')->useCurrent();

            // Índices (AC5)
            $table->index('tenant_id');
            $table->index('tenant_signup_id');

            $table->foreign('tenant_signup_id')
                ->references('id')
                ->on('tenant_signups')
                ->nullOnDelete();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->nullOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms_acceptances');
    }
};
