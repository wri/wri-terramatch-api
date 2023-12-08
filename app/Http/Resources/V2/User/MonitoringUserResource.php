<?php

namespace App\Http\Resources\V2\User;

use App\Http\Resources\V2\Organisation\MonitoringOrganisationResource;
use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Http\Resources\V2\Organisation\OrganisationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitoringUserResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $this->user;

        return [
            'uuid' => $user->uuid ?? null,
            'user_type' => $user->role ?? null,
            'job_role' => $user->job_role ?? null,
            'first_name' => $user->first_name ?? null,
            'last_name' => $user->last_name ?? null,
            'email_address' => $this->email_address,
            'organisation' => is_null($user) ? null : new OrganisationLiteResource($user->organisation),
            'status' => is_null($this->accepted_at) ? 'Pending' : 'Accepted',
        ];
    }
}
