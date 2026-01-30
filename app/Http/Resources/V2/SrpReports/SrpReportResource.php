<?php

namespace App\Http\Resources\V2\SrpReports;

use App\Http\Resources\V2\Projects\ProjectLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SrpReportResource extends JsonResource
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
            'restoration_partners_description' => $this->restoration_partners_description,
            'total_unique_restoration_partners' => $this->total_unique_restoration_partners,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'project' => new ProjectLiteResource($this->project),
            'task_uuid' => $this->task->uuid,
        ];

        return $this->appendFilesToResource($data);
    }
}
