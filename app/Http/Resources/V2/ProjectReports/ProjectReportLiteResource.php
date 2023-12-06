<?php

namespace App\Http\Resources\V2\ProjectReports;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Http\Resources\V2\Projects\ProjectLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectReportLiteResource extends JsonResource
{
    protected bool $withReportingTask = false;

    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'framework_key' => $this->framework_key,
            'framework_uuid' => $this->framework_uuid,
            'report_title' => $this->report_title,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'update_request_status' => $this->update_request_status,
            'completion_status' => $this->completion_status,
            'readable_completion_status' => $this->readable_completion_status,
            'due_at' => $this->due_at,
            'submitted_at' => $this->submitted_at,
            'organisation' => new OrganisationLiteResource($this->organisation),
            'project' => new ProjectLiteResource($this->project),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'task_uuid' => $this->withReportingTask ? $this->task_uuid : null,
        ];

        return $data;
    }

    public function withReportingTask(): self
    {
        $this->withReportingTask = true;

        return $this;
    }
}
