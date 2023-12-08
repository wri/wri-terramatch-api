<?php

namespace App\Http\Resources\V2\Nurseries;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Http\Resources\V2\Projects\ProjectLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class NurseryLiteResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'project' => new ProjectLiteResource($this->project),
            'organisation' => new OrganisationLiteResource($this->organisation),
            'establishment_date' => $this->establishment_date,
            'framework_key' => $this->framework_key,
            'framework_uuid' => $this->framework_uuid,
            'status' => $this->status,
            'update_request_status' => $this->update_request_status,
            'readable_status' => $this->readable_status,
            'start_date' => $this->start_date,
            'created_at' => $this->created_at,
            'seedlings_grown_count' => $this->seedlings_grown_count,
            'nursery_reports_total' => $this->nursery_reports_total,
        ];

        return $data;
    }
}
