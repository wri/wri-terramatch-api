<?php

namespace App\Http\Resources\V2\User;

use App\Http\Resources\V2\ImpactStory\ImpactStoryResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ImpactStoriesCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => ImpactStoryResource::collection($this->collection)];
    }
}
