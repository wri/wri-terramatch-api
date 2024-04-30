<?php

namespace App\Resources;

use App\Http\Resources\V2\Organisation\MonitoringOrganisationResource;
use App\Http\Resources\V2\Organisation\OrganisationResource;
use App\Models\User as UserModel;
use App\Models\V2\User as V2UserModel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserRoleResource
{
    public function __construct(UserModel $user)
    {
        $v2user = V2UserModel::find($user->id);
        $this->id = $user->id;
        $this->organisation_id = $user->organisation_id;
        $this->organisation_name = $this->getOrganisationName($user);
        $this->my_organisation = $this->getV2MyOrganisation($v2user);
        $this->my_monitoring_organisations = $this->getV2MonitoringOrganisations($v2user);
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email_address = $user->email_address;
        $this->role = $user->primary_role ? $user->primary_role->name : '';
        $this->country = $user->country;
        $this->program = $user->program;
        $this->email_address_verified_at = $user->email_address_verified_at;
        $this->last_logged_in_at = $user->last_logged_in_at;
        $this->job_role = $user->job_role;
        $this->twitter = $user->twitter;
        $this->facebook = $user->facebook;
        $this->linkedin = $user->linkedin;
        $this->instagram = $user->instagram;
        $this->avatar = $user->avatar;
        $this->whatsapp_phone = $user->whatsapp_phone;
        $this->phone_number = $user->phone_number;
        $this->banners = $user->banners;
        $this->has_ppc_projects = $user->programmes->count() > 0;
        $this->has_terrafund_projects = $user->terrafundProgrammes->count() > 0;
    }

    private function getOrganisationName($user)
    {
        if (empty($user->organisation)) {
            return null;
        }

        return data_get($user->organisation, 'name');
    }

    private function getV2MyOrganisation($v2user): ?OrganisationResource
    {
        if ($v2user->organisation) {
            return new OrganisationResource($v2user->my_organisation);
        }

        return null;
    }

    private function getV2MonitoringOrganisations($v2user): ?AnonymousResourceCollection
    {
        if (count($v2user->organisations) > 0) {
            return MonitoringOrganisationResource::collection($v2user->organisations);
        }

        return null;
    }
}
