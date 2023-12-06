<?php

namespace App\Http\Resources\V2\Projects\Monitoring;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMonitoringResource extends JsonResource
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

            'total_hectares' => $this->total_hectares,
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

            'start_date' => $this->start_date,
            'end_date' => $this->end_date,

            'last_updated' => $this->last_updated,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
