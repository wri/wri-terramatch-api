<?php

namespace App\Http\Controllers\V2\Disturbances;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Disturbances\UpdateDisturbanceRequest;
use App\Http\Resources\V2\Disturbances\DisturbanceResource;
use App\Models\V2\Disturbance;

class UpdateDisturbanceController extends Controller
{
    public function __invoke(Disturbance $disturbance, UpdateDisturbanceRequest $updateDisturbanceRequest): DisturbanceResource
    {
        $this->authorize('update', $disturbance->disturbanceable);
        $disturbance->update($updateDisturbanceRequest->validated());
        $disturbance->save();

        return new DisturbanceResource($disturbance);
    }
}
