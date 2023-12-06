<?php

namespace App\Http\Resources\V2\Projects;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectInviteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'project_id' => $this->project->id,
            'email_address' => $this->email_address,
            'accepted_at' => $this->accepted_at,
            'created_at' => $this->created_at,
        ];
    }
}
