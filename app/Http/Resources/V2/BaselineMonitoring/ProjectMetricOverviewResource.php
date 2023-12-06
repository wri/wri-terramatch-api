<?php

namespace App\Http\Resources\V2\BaselineMonitoring;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMetricOverviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'readable_status' => $this->readable_status,

            'ha_mangrove' => $this->ha_mangrove,
            'ha_assisted' => $this->ha_assisted,
            'ha_agroforestry' => $this->ha_agroforestry,
            'ha_reforestation' => $this->ha_reforestation,
            'ha_peatland' => $this->ha_peatland,
            'ha_riparian' => $this->ha_riparian,
            'ha_enrichment' => $this->ha_enrichment,
            'ha_nucleation' => $this->ha_nucleation,
            'ha_silvopasture' => $this->ha_silvopasture,
            'ha_direct' => $this->ha_direct,
            'tree_count' => $this->tree_count,
            'tree_cover' => $this->tree_cover,
            'tree_cover_loss' => $this->tree_cover_loss,
            'carbon_benefits' => $this->carbon_benefits,
            'number_of_esrp' => $this->number_of_esrp,
            'field_tree_count' => $this->field_tree_count,
            'field_tree_regenerated' => $this->field_tree_regenerated,
            'field_tree_survival_percent' => $this->field_tree_survival_percent,

            'cover_url' => $this->cover_image_url,
            'gallery' => $this->gallery_files,
            'support_files' => $this->support_files,

            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
