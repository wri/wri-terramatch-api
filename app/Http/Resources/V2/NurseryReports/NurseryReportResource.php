<?php

namespace App\Http\Resources\V2\NurseryReports;

use App\Http\Resources\V2\Nurseries\NurseryLiteResource;
use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Http\Resources\V2\Projects\ProjectLiteResource;
use App\Http\Resources\V2\User\UserLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class NurseryReportResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'framework_key' => $this->framework_key,
            'framework_uuid' => $this->framework_uuid,
            'report_title' => $this->report_title,
            'project_report_title' => data_get($this->project, 'report_title'),

            'nursery' => new NurseryLiteResource($this->nursery),
            'organisation' => new OrganisationLiteResource($this->organisation),
            'due_at' => $this->due_at,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'update_request_status' => $this->update_request_status,
            'readable_update_request_status' => $this->readable_update_request_status,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'nothing_to_report' => $this->nothing_to_report,
            'readable_completion_status' => $this->readable_completion_status,
            'title' => $this->title,
            'seedlings_young_trees' => $this->seedlings_young_trees,
            'interesting_facts' => $this->interesting_facts,
            'site_prep' => $this->site_prep,
            'shared_drive_link' => $this->shared_drive_link,
            'created_by' => new UserLiteResource($this->createdBy),
            'approved_by' => new UserLiteResource($this->approvedBy),
            'project' => new ProjectLiteResource($this->nursery->project),
            'task_uuid' => $this->task_uuid,
            'submitted_at' => $this->submitted_at,
            'migrated' => ! empty($this->old_model),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'project_report' => $this->project_report,
        ];

        return $this->appendFilesToResource($data);
    }
}
