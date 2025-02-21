<?php

namespace App\Http\Resources\V2\ImpactStory;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ImpactStoriesCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => ImpactStoryLiteResource::collection($this->collection)];
    }
}
