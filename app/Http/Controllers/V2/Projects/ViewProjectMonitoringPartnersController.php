<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\User\AssociatedUserResource;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectInvite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ViewProjectMonitoringPartnersController extends Controller
{
    public function __invoke(Request $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('read', $project);

        $invites = ProjectInvite::with('user')
            ->where('project_id', $project->id)
            ->orderByDesc('id')
            ->orderByDesc('accepted_at')
            ->get();

        $uniques = $invites->unique('email_address');

        $results = $uniques->map(function ($invite) use ($invites){
            if(!is_null($invite->accepted_at)){
                return $invite;
            }

            $accepted = $invites->where('email_address', $invite->email_address)->whereNotNull('accepted_at')->first();

            return is_null($accepted) ? $invite : $accepted;
        });

        return AssociatedUserResource::collection($results);
    }
}
