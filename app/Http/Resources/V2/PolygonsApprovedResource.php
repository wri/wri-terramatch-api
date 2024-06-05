<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class PolygonsApprovedResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return bool
     */
    public function toArray($request)
    {
        return $this->resource;
    }
}
