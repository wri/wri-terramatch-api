<?php

namespace App\Services;

use App\Http\Resources\V2\Applications\ApplicationResource;
use App\Models\V2\Forms\Application;

class ApplicationService
{
    public function getApplicationPayload(Application $application)
    {
        return new ApplicationResource($application);
    }
}
