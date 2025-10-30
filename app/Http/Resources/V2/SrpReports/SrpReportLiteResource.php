<?php

namespace App\Http\Resources\V2\SrpReports;

use Illuminate\Http\Resources\Json\JsonResource;

class SrpReportLiteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'project_name' => $this->project->name ?? null,
            'status' => $this->status,
            'update_request_status' => $this->update_request_status,
            'other_restoration_partners_description' => $this->other_restoration_partners_description,
            'total_unique_restoration_partners' => $this->total_unique_restoration_partners,
            'submitted_at' => $this->submitted_at,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
