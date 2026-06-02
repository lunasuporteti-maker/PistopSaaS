<?php

namespace App\Jobs;

use App\Mail\PagamentoConfirmadoMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPagamentoConfirmadoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public int    $tenantId,
        public string $nomePlano,
        public float  $valor,
        public string $proximoVencimento,
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (! $tenant) {
            Log::warning('[PagamentoConfirmado] Tenant não encontrado', ['tenant_id' => $this->tenantId]);

            return;
        }

        // Busca o admin principal do tenant para receber o email
        $admin = User::where('tenant_id', $tenant->id)
            ->where('perfil', 'admin')
            ->whereNotNull('email')
            ->orderBy('id')
            ->first();

        if (! $admin) {
            Log::warning('[PagamentoConfirmado] Admin sem email encontrado', ['tenant_id' => $this->tenantId]);

            return;
        }

        Mail::to($admin->email)->send(new PagamentoConfirmadoMail(
            tenant: $tenant,
            nomePlano: $this->nomePlano,
            valor: $this->valor,
            proximoVencimento: $this->proximoVencimento,
            emailDestinatario: $admin->email,
            nomeDestinatario: $admin->name,
        ));

        Log::info('[PagamentoConfirmado] Email enviado', ['tenant' => $tenant->nome, 'email' => $admin->email]);
    }
}
