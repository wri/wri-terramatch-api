<?php

namespace App\Http\Resources\V2\Organisation;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrganisationsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => OrganisationLiteResource::collection($this->collection)];
    }
}
