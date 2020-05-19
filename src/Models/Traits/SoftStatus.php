<?php

namespace Silverd\OhMyLaravel\Models\Traits;

use Illuminate\Database\Eloquent\Concerns\HasGlobalScopes;
use Illuminate\Database\Eloquent\Builder;

trait SoftStatus
{
    use HasGlobalScopes;

    public static function bootSoftStatus()
    {
        static::addGlobalScope('status', function (Builder $builder) {
            $builder->where('status', 1);
        });
    }
}
