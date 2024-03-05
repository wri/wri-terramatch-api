<?php

namespace App\Http\Resources\V2\Nurseries;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Http\Resources\V2\Projects\ProjectLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class NurseryResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'framework_key' => $this->framework_key,
            'framework_uuid' => $this->framework_uuid,
            'name' => $this->name,
            'project' => new ProjectLiteResource($this->project),
            'organisation' => new OrganisationLiteResource($this->organisation),
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'update_request_status' => $this->update_request_status,
            'readable_update_request_status' => $this->readable_update_request_status,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'type' => $this->type,
            'establishment_date' => $this->start_date,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'seedling_grown' => $this->seedling_grown,
            'planting_contribution' => $this->planting_contribution,
            'seedlings_grown_count' => $this->seedlings_grown_count,
            'nursery_reports_total' => $this->nursery_reports_total,
            'overdue_nursery_reports_total' => $this->overdue_nursery_reports_total,
            'migrated' => ! empty($this->old_model),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $this->appendFilesToResource($data);
    }
}
