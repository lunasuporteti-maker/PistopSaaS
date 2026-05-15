<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Global scope: toda query filtra pelo tenant atual automaticamente
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (! app()->bound(Tenant::class)) {
                return;
            }
            $tenant = app(Tenant::class);
            if ($tenant instanceof Tenant) {
                $builder->where(
                    (new static)->getTable() . '.tenant_id',
                    $tenant->id
                );
            }
        });

        // Ao criar, seta tenant_id automaticamente
        static::creating(function ($model) {
            if (empty($model->tenant_id) && app()->bound(Tenant::class)) {
                $tenant = app(Tenant::class);
                if ($tenant instanceof Tenant) {
                    $model->tenant_id = $tenant->id;
                }
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Bypass do scope para queries cross-tenant (super admin)
    public static function semTenant(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
