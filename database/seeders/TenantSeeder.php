<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::firstOrCreate(
            ['slug' => env('DEFAULT_TENANT_SLUG', 'autofix')],
            [
                'nome'  => env('APP_NAME', 'AutoFix'),
                'plano' => 'basico',
                'ativo' => true,
            ]
        );
    }
}
