<?php

namespace App\Http\Resources\V2\Tasks;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Http\Resources\V2\Projects\ProjectLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'period_key' => $this->period_key,
            'due_at' => $this->due_at,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'project' => new ProjectLiteResource($this->project),
            'completion_status' => $this->completion_status,
//            'organisation' => new OrganisationLiteResource($this->organisation),
        ];
    }
}
