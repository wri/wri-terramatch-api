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

        $organisationId = $user->organisation_id;
        $projectIds = $user->projects()->pluck('v2_projects.id')->toArray();

        $qry = Action::query()
            ->with('targetable')
            ->pending();

        if (count($projectIds) > 0) {
            $qry->where(function ($query) use ($organisationId, $projectIds) {
                $query->whereIn('project_id', $projectIds)
                    ->orWhere('organisation_id', $organisationId);
            });
        } else {
            $qry->where('organisation_id', $organisationId);
        }

        $actions = $qry->get();

        return ActionResource::collection($actions);
    }
}
