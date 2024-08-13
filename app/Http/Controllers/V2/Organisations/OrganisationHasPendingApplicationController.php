<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Http\Resources\V2\Organisation\PendingApplicationResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrganisationHasPendingApplicationController
{
    public function __invoke() : PendingApplicationResource
    {
        $user = Auth::user();
        $userOrganisation = $user->organisation()->first();
        if ($userOrganisation !== null) {
            return new PendingApplicationResource(
                false,
                'User already has an organisation'
            );
        }
        $requestedOrganization = $user->organisations()->first();
        Log::info($requestedOrganization);
        if ($requestedOrganization === null) {
            return new PendingApplicationResource(
                false,
                'User has no pending applications'
            );
        }
        Log::info($requestedOrganization);
        return new PendingApplicationResource(
            true,
            'Organisation has pending application',
            $requestedOrganization,
            $requestedOrganization->pivot->status
        );
    }
}
