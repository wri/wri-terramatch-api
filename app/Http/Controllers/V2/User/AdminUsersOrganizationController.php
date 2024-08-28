<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Http\Request;

class AdminUsersOrganizationController extends Controller
{
    public function __invoke(Organisation $organisation, Request $request)
    {
        $this->authorize('readAll', User::class);
        $ownersApproved = $organisation->owners;
        $partnersStatus = $organisation->partners;
        $usersFinallyWithOwners = $ownersApproved->each(function ($user) {
            $user['status'] = 'approved';
        });
        $usersFinallyWithPartners = $partnersStatus->each(function ($user) {
            $user['status'] = $user->pivot->status;
        });

        return response()->json($usersFinallyWithOwners->merge($usersFinallyWithPartners));
    }
}
