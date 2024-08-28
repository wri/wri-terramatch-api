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
        $OwnersWithRequestStatus = $organisation->owners->map(function ($user) {
            $user['status'] = 'approved';
            return $user;
        });
        $partnersWithRequestStatus = $organisation->partners->map(function ($user) {
            $user['status'] = $user->pivot->status;
            return $user;
        });

        $usersOrganisation = $OwnersWithRequestStatus->merge($partnersWithRequestStatus);
        return response()->json($usersOrganisation);
    }
}
