<?php

namespace App\Http\Resources\V2\SitePolygon;

use Illuminate\Http\Resources\Json\JsonResource;

class SitePolygonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'poly_name' => $this->poly_name,
            'status' => $this->status,
            'date_created' => $this->date_created,
            'created_by' => $this->created_by,
        ];
    }
}
