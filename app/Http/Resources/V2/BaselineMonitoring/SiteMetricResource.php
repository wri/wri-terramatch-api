<?php

namespace App\Http\Resources\V2\BaselineMonitoring;

use Illuminate\Http\Resources\Json\JsonResource;

class SiteMetricResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'readable_status' => $this->readable_status,

            'tree_count' => $this->tree_count,
            'tree_cover' => $this->tree_cover,

            'field_tree_count' => $this->field_tree_count,

            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
