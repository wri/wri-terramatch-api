<?php

namespace App\Http\Resources\V2\DisturbanceReports;

use App\Http\Resources\V2\Projects\ProjectResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DisturbanceReportResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'due_at' => $this->due_at,
            'status' => $this->status,
            'update_request_status' => $this->update_request_status,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'nothing_to_report' => $this->nothing_to_report,
            'submitted_at' => $this->submitted_at,
            'date_of_incident' => $this->date_of_incident,
            'intensity' => $this->intensity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'project' => new ProjectResource($this->project),
            'disturbance_type' => $this->disturbance_type,
            'disturbance_subtype' => $this->disturbance_subtype,
            'extent' => $this->extent,
            'people_affected' => $this->people_affected,
            'date_of_disturbance' => $this->date_of_disturbance,
            'monetary_damage' => $this->monetary_damage,
            'description' => $this->description,
            'action_description' => $this->action_description,
            'property_affected' => $this->property_affected,
        ];

        return $this->appendFilesToResource($data);
    }
}
