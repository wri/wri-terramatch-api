<?php

namespace App\Models\Traits;

use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @method morphMany(string $class, string $string)
 */
trait HasUpdateRequests {
    public function updateRequests(): MorphMany
    {
        return $this->morphMany(UpdateRequest::class, 'updaterequestable');
    }
}