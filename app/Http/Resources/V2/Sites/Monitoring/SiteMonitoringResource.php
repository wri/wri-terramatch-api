<?php

namespace App\Http\Resources\V2\Sites\Monitoring;

use Illuminate\Http\Resources\Json\JsonResource;

class SiteMonitoringResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'tree_count' => $this->tree_count,
            'tree_cover' => $this->tree_cover,
            'field_tree_count' => $this->field_tree_count,
            'measurement_date' => $this->measurement_date,
            'last_updated' => $this->last_updated,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
