<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasFrameworkKey
{
    public function scopePPC($query): Builder
    {
        return $query->where('framework_key', 'ppc');
    }

    public function scopeTerrafund($query): Builder
    {
        return $query->where('framework_key', 'terrafund');
    }
}
