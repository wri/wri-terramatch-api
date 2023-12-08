<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasUuid
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    public function scopeIsUuid($query, $uuid): Builder
    {
        return $query->where('uuid', $uuid);
    }
}
