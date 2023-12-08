<?php

namespace App\Http\Resources\V2\ProjectPitches;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectPitchesCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => ProjectPitchResource::collection($this->collection)];
    }
}
