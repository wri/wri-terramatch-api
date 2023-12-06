<?php

namespace App\Http\Resources\V2\Polygons;

use Illuminate\Http\Resources\Json\JsonResource;

class GeojsonResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => data_get($this, 'uuid'),
            'name' => data_get($this, 'name'),
            'geojson' => data_get($this, 'boundary_geojson'),
            'created_at' => data_get($this, 'created_at'),
        ];
    }
}
