<?php

namespace App\Http\Resources\V2\Disturbances;

use Illuminate\Http\Resources\Json\JsonResource;

class DisturbanceResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'collection' => $this->collection,
            'type' => $this->type,
            'intensity' => $this->intensity,
            'extent' => $this->extent,
            'description' => $this->description,
        ];
    }
}
