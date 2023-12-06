<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\User\UsersCollection;
use App\Models\V2\Organisation;
use Illuminate\Http\Request;

class OrganisationApprovedUsersController extends Controller
{
    public function __invoke(Organisation $organisation, Request $request): UsersCollection
    {
        $this->authorize('viewUsers', $organisation);

        return  new UsersCollection($organisation->authorisedUsers());
    }
}
