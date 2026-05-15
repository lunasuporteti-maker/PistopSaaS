<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL', 'admin@pitstop.com.br');
        $password = env('ADMIN_PASSWORD', 'Mudar@123');

        User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => 'Administrador',
                'email'    => $email,
                'password' => Hash::make($password),
                'perfil'   => 'admin',
                'ativo'    => true,
            ]
        );
    }
}
