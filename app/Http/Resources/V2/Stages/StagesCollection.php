<?php

namespace App\Http\Resources\V2\Stages;

use Illuminate\Http\Resources\Json\ResourceCollection;

class StagesCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => StageResource::collection($this->collection)];
    }
}
