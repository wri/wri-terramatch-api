<?php

namespace App\Http\Controllers\V2\Disturbances;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Disturbances\DisturbanceResource;
use App\Models\V2\Disturbance;

class DeleteDisturbanceController extends Controller
{
    public function __invoke(Disturbance $disturbance): DisturbanceResource
    {
        $this->authorize('update', $disturbance->disturbanceable);
        $disturbance->delete();
        $disturbance->save();

        return new DisturbanceResource($disturbance);
    }
}
