<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasStatus
{
    public function scopeIsStatus($query, $status): Builder
    {
        return $query->where('status', $status);
    }

    public function getReadableStatusAttribute(): ?string
    {
        if (empty($this->status)) {
            return null;
        }

        return data_get(static::$statuses, $this->status, 'Unknown');
    }
}
