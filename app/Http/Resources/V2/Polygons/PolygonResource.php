<?php

namespace App\Http\Resources\V2\Polygons;

use Illuminate\Http\Resources\Json\JsonResource;

class PolygonResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'area' => $this->area,
            'perimeter' => $this->perimeter,
            'status' => $this->status,
        ];
    }
}
