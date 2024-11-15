<?php

namespace App\Http\Resources\V2\Projects;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectLiteResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'is_test' => $this->is_test,
            'ppc_external_id' => $this->ppc_external_id ?? $this->id,
            'framework_key' => $this->framework_key,
            'framework_uuid' => $this->framework_uuid,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'update_request_status' => $this->update_request_status,
            'name' => $this->name,
            'country' => $this->country,
            'organisation' => new OrganisationLiteResource($this->organisation),
            'planting_start_date' => $this->planting_start_date,
            'has_monitoring_data' => $this->has_monitoring_data,
            'project_reports_total' => $this->project_reports_total,
        ];

        return $data;
    }
}
