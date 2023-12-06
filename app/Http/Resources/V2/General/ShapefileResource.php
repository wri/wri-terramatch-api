<?php

namespace App\Http\Resources\V2\General;

use Illuminate\Http\Resources\Json\JsonResource;

class ShapefileResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'shapefileable_type' => $this->shapefileable_type,
            'shapefileable_id' => $this->shapefileable_id,
            'geojson' => $this->geojson,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
