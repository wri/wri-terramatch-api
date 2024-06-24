<?php

namespace App\Http\Resources\V2\Sites;

use App\Http\Resources\V2\Projects\ProjectLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteLiteResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'ppc_external_id' => $this->ppc_external_id ?? $this->id,
            'name' => $this->name,
            'project' => new ProjectLiteResource($this->project),
            'framework_key' => $this->framework_key,
            'framework_uuid' => $this->framework_uuid,
            'description' => $this->description,
            'control_site' => $this->control_site,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'update_request_status' => $this->update_request_status,
            'number_of_trees_planted' => $this->trees_planted_count,
            'start_date' => $this->start_date,
            'created_at' => $this->created_at,
            'site_reports_total' => $this->site_reports_total,
            'has_monitoring_data' => empty($this->has_monitoring_data) ? false : true,
        ];

        return $data;
    }
}
