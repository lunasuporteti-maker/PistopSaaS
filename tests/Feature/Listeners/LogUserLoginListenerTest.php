<?php

namespace Tests\Feature\Listeners;

use App\Listeners\LogUserLogin;
use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stories 7.1 / 7.2 — Listener LogUserLogin: registra login, mantém apenas 3, IP hasheado.
 */
class LogUserLoginListenerTest extends TestCase
{
    use RefreshDatabase;

    private function dispararLogin(User $user): void
    {
        $listener = new LogUserLogin();
        $listener->handle(new Login('web', $user, false));
    }

    public function test_cria_registro_ao_logar(): void
    {
        $user = User::factory()->create();

        $this->dispararLogin($user);

        $this->assertDatabaseHas('user_login_logs', ['user_id' => $user->id]);
    }

    public function test_registra_logged_in_at_com_timestamp_atual(): void
    {
        $user = User::factory()->create();
        $agora = now()->startOfMinute();

        $this->dispararLogin($user);

        $log = UserLoginLog::where('user_id', $user->id)->first();
        $this->assertTrue($log->logged_in_at->gte($agora));
    }

    public function test_nao_acumula_mais_de_3_registros(): void
    {
        $user = User::factory()->create();

        $this->dispararLogin($user);
        $this->dispararLogin($user);
        $this->dispararLogin($user);
        $this->dispararLogin($user); // 4º login
        $this->dispararLogin($user); // 5º login

        $total = UserLoginLog::where('user_id', $user->id)->count();
        $this->assertSame(3, $total);
    }

    public function test_registros_mantidos_sao_os_mais_recentes(): void
    {
        $user = User::factory()->create();

        // Insere 2 logs antigos diretamente
        UserLoginLog::create(['user_id' => $user->id, 'logged_in_at' => now()->subDays(10)]);
        UserLoginLog::create(['user_id' => $user->id, 'logged_in_at' => now()->subDays(5)]);

        // 3 novos via listener
        $this->dispararLogin($user);
        $this->dispararLogin($user);
        $this->dispararLogin($user);

        $this->assertSame(3, UserLoginLog::where('user_id', $user->id)->count());

        // O registro de 10 dias atrás deve ter sido removido
        $this->assertDatabaseMissing('user_login_logs', [
            'user_id'      => $user->id,
            'logged_in_at' => now()->subDays(10)->toDateTimeString(),
        ]);
    }

    public function test_ip_armazenado_e_hash_nao_texto_puro(): void
    {
        $user = User::factory()->create();

        $this->dispararLogin($user);

        $log = UserLoginLog::where('user_id', $user->id)->first();

        // Hash sha256 = 64 hex chars; nunca deve ser um IPv4/IPv6 legível
        $this->assertSame(64, strlen($log->ip_address));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $log->ip_address);
        $this->assertStringNotContainsString('.', $log->ip_address); // não é IPv4
    }

    public function test_listener_nao_propaga_excecao_em_caso_de_falha(): void
    {
        // Usuário sem ID (make sem save) → getKey() retorna null → falha no insert
        $user = User::factory()->make(['id' => null]);

        $this->expectNotToPerformAssertions();

        $listener = new LogUserLogin();
        $listener->handle(new Login('web', $user, false)); // não deve lançar
    }
}
