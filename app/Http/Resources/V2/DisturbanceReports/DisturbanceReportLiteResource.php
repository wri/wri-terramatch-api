<?php

namespace App\Http\Resources\V2\DisturbanceReports;

use Illuminate\Http\Resources\Json\JsonResource;

class DisturbanceReportLiteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'project_name' => $this->project->name ?? null,
            'status' => $this->status,
            'update_request_status' => $this->update_request_status,
            'date_of_incident' => $this->date_of_incident,
            'intensity' => $this->intensity,
            'submitted_at' => $this->submitted_at,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
