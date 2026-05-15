<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Tenant demo para desenvolvimento local
        Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'nome'  => 'PitStop Demo',
                'slug'  => 'demo',
                'plano' => 'basico',
                'ativo' => true,
            ]
        );
    }
}
