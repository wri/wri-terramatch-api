<?php

namespace App\Http\Resources\V2\User;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class AssociatedUserResource extends JsonResource
{
    public function toArray($request)
    {
        // The resource can either be a User or a ProjectInvite
        $isUser = $this->resource instanceof User;
        $user = $isUser ? $this->resource : $this->user;

        return [
            'uuid' => $user->uuid ?? null,
            'user_type' => $user->role ?? null,
            'job_role' => $user->job_role ?? null,
            'first_name' => $user->first_name ?? null,
            'last_name' => $user->last_name ?? null,
            'email_address' => $this->email_address,
            'organisation' => is_null($user) ? null : new OrganisationLiteResource($user->organisation),
            'status' => $isUser ? 'Accepted' : (is_null($this->accepted_at) ? 'Pending' : 'Accepted'),
        ];
    }
}
