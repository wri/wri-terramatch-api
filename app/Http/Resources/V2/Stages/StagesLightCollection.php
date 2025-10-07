<?php

namespace App\Http\Resources\V2\Stages;

class StagesLightCollection extends StagesCollection
{
    public function toArray($request)
    {
        return ['data' => StageLiteResource::collection($this->collection)];
    }
}
