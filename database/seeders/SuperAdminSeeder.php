<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Cria o usuário super_admin da plataforma IAQueAtende.
 *
 * Uso LOCAL:  php artisan db:seed --class=SuperAdminSeeder
 * Produção:   php artisan db:seed --class=SuperAdminSeeder  (seguro — usa firstOrCreate)
 *
 * IMPORTANTE: Este seeder usa firstOrCreate — nunca sobrescreve um usuário existente.
 * Altere a senha após o primeiro login em produção.
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['username' => 'iaqueatende'],
            [
                'name'       => 'IAQueAtende Admin',
                'email'      => 'admin@iaqueatende.com.br',
                'username'   => 'iaqueatende',
                'password'   => Hash::make('PitStop@2026!'),  // ← ALTERE EM PRODUÇÃO
                'perfil'     => 'super_admin',
                'tenant_id'  => null,
                'ativo'      => true,
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->command->info("✅ Super admin criado: {$user->username} / PitStop@2026!");
        } else {
            $this->command->info("ℹ️  Super admin já existe: {$user->username}");
        }
    }
}
