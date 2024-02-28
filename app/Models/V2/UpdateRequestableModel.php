<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface UpdateRequestableModel
{
    public function updateRequests(): MorphMany;
}