<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL', 'admin@pitstop.com.br');
        $password = env('ADMIN_PASSWORD', 'Mudar@123');
        $slug     = env('DEFAULT_TENANT_SLUG', 'demo');

        // Garante que o tenant existe antes de criar o admin
        $tenant = Tenant::firstOrCreate(
            ['slug' => $slug],
            [
                'nome'  => env('APP_NAME', 'PitStop'),
                'plano' => 'basico',
                'ativo' => true,
            ]
        );

        // Bypassa o global scope pois ainda não há tenant no contexto do seeder
        User::withoutGlobalScope('tenant')->firstOrCreate(
            ['email' => $email],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Administrador',
                'email'     => $email,
                'password'  => Hash::make($password),
                'perfil'    => 'admin',
                'ativo'     => true,
            ]
        );
    }
}
