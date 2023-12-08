<?php

namespace App\Http\Resources\V2\UpdateRequests;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Http\Resources\V2\ProjectReports\ProjectReportLiteResource;
use App\Http\Resources\V2\User\UserLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'framework_key' => $this->framework_key,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'content' => $this->content,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'project' => new ProjectReportLiteResource($this->project),
            'organisation' => new OrganisationLiteResource($this->organisation),
            'created_by' => new UserLiteResource($this->created_by),
        ];
    }
}
