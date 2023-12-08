<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\User\UsersCollection;
use App\Models\V2\Organisation;
use Illuminate\Http\Request;

class OrganisationListRequestedUsersController extends Controller
{
    public function __invoke(Request $request, Organisation $organisation)
    {
        $this->authorize('listUsers', $organisation);

        $collection = $organisation->usersRequested()
            ->orderBy('first_name')
            ->paginate(config('app.pagination_default', 15));

        return new UsersCollection($collection);
    }
}
