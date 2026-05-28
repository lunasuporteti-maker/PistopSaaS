<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de signups de onboarding self-service (PRD 03, seção 8).
     *
     * Armazena os dados de cadastro antes da confirmação de e-mail.
     * Após confirmação, gera o tenant correspondente (tenant_id é preenchido).
     * Enums implementados como string + constantes no Model (portabilidade SQLite/PG).
     */
    public function up(): void
    {
        Schema::create('tenant_signups', function (Blueprint $table) {
            $table->id();

            // Dados da oficina
            $table->string('nome_oficina', 200);
            $table->string('slug_desejado', 60);
            $table->string('cnpj', 18)->nullable();
            $table->string('telefone', 20);
            $table->string('cidade', 120);
            $table->string('uf', 2);

            // Dados do responsável
            $table->string('nome_completo', 200);
            $table->string('email', 150);
            $table->string('senha_hash', 255);

            // Plano escolhido: trial | padrao (enum como string)
            $table->string('plano_escolhido', 30)->default('trial');

            // Consentimentos LGPD
            $table->boolean('consentimento_emails_transacionais')->default(true);
            $table->boolean('consentimento_marketing')->default(false);

            // Token de confirmação de e-mail (UUID v4 — 36 chars)
            $table->string('token_confirmacao', 36)->unique();
            $table->timestamp('token_expira_em')->nullable();

            // Status: pending_email_confirmation | confirmed | expired | abandoned
            $table->string('status', 40)->default('pending_email_confirmation');

            // Tenant gerado após confirmação (nullable até confirmar)
            $table->unsignedBigInteger('tenant_id')->nullable();

            // Auditoria de origem
            $table->string('ip_origem', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Índices (AC5)
            $table->index('email');
            $table->index('status');
            $table->index('tenant_id');

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_signups');
    }
};
