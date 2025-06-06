<?php

namespace App\Http\Resources\V2\User;

use App\Http\Resources\V2\Organisation\MonitoringOrganisationResource;
use App\Http\Resources\V2\Organisation\OrganisationResource;
use App\Http\Resources\V2\Projects\ProjectLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'job_role' => $this->job_role,
            'role' => $this->primary_role->name,
            'country' => $this->country,
            'program' => $this->program,

            'direct_frameworks' => $this->frameworks()->pluck('slug'),
            'all_frameworks' => $this->my_frameworks_slug,

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email_address' => $this->email_address,
            'phone_number' => $this->phone_number,
            'whatsapp_phone' => $this->whatsapp_phone,

            'organisation' => new OrganisationResource($this->my_organisation),
            'monitoring_organisations' => MonitoringOrganisationResource::collection($this->organisations),

            'managed_projects' => $this->managedProjects == null ? null : ProjectLiteResource::collection($this->managedProjects),
            'monitoring_projects' => $this->projects == null ? null : ProjectLiteResource::collection($this->projects),

            'last_logged_in_at' => $this->last_logged_in_at,
            'email_address_verified_at' => $this->email_address_verified_at,
            'verified' => ! empty($this->email_address_verified_at),
            'date_added' => $this->created_at,

            'banners' => $this->banners,

            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
