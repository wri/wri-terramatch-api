<?php

namespace App\Models\Traits;

use App\Models\V2\UpdateRequests\UpdateRequest;
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

    public function getReadableUpdateRequestStatusAttribute(): ?string
    {
        if (empty($this->update_request_status)) {
            return null;
        }

        return data_get(UpdateRequest::$statuses, $this->update_request_status, 'Unknown');
    }
}
