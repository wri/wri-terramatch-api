<?php

namespace App\Http\Resources\V2\FundingProgrammes;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FundingProgrammeCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => FundingProgrammeLiteResource::collection($this->collection)];
    }
}
