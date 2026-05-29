<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Tier do plano: pro = R$99,90 | pro_max = R$157,50 (fotos no portal, etc)
            $table->string('plano_tier', 20)->default('pro')->after('plano');
            // Desconto percentual manual (0-100), aplicado pelo super admin
            $table->unsignedTinyInteger('desconto_percentual')->default(0)->after('plano_tier');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['plano_tier', 'desconto_percentual']);
        });
    }
};
