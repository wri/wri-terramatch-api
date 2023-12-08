<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasTypes
{
    public function scopeIsType($query, $type): Builder
    {
        return $query->where('type', $type);
    }

    public function getReadableTypeAttribute(): ?string
    {
        if (empty($this->type)) {
            return null;
        }

        return data_get(static::$types, $this->type, 'Unknown');
    }
}
