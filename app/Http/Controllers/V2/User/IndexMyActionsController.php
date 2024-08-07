<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\User\ActionResource;
use App\Models\V2\Action;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class IndexMyActionsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $user = Auth::user();

        $projectIds = $user->projects()->pluck('v2_projects.id')->toArray();

        $qry = Action::query()
            ->with('targetable')
            ->pending()
            ->projectIds($projectIds);

        $actions = $qry->get();

        return ActionResource::collection($actions);
    }
}
