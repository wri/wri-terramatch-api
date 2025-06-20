<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SitePolygonLightResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'poly_name' => $this->poly_name,
            'practice' => $this->practice,
            'target_sys' => $this->target_sys,
            'distr' => $this->distr,
            'plantstart' => $this->plantstart,
            'source' => $this->source,
            'status' => $this->status,
            'validation_status' => $this->validation_status,
            'uuid' => $this->uuid,
            'poly_id' => $this->poly_id,
            'primary_uuid' => $this->primary_uuid,
            'calc_area' => $this->calc_area,
            'num_trees' => $this->num_trees,
            'site_id' => $this->site_id,
        ];
    }
}
