<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\User\AssociatedUserResource;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectInvite;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

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

        $projectUsers = $project->users()->wherePivot('is_managing', false)->get();

        // TODO (NJC): When moving to v3, inspect this side effect and see if it's really necessary. I'm not convinced
        //   creating an invite for pending users is really needed.
        foreach ($projectUsers as $projectUser) {
            if ($projectUser->pivot->status == "active") continue;

            $invite = $invites->firstWhere('email_address', $projectUser->email_address);
            if (!$invite) {
                $token = $this->generateUniqueToken();
                $data['project_id'] = $project->id;
                $data['token'] = $token;
                $data['email_address'] = $projectUser->email_address;
                $newInvite = $project->invites()->create($data);
                $invites->push($newInvite);
            }
        }

        $projectUsersEmails = $projectUsers->pluck('email_address');
        $uniques = $projectUsersEmails->push($invites->pluck('email_address'))->flatten()->unique();

        $results = $uniques
            ->map(function ($emailAddress) use ($projectUsers, $invites) {
                /** @var User $user */
                $user = $projectUsers->firstWhere("email_address", $emailAddress);
                if ($user?->primaryRole?->name == "project-manager") return null;

                $invite = $invites->firstWhere('email_address', $emailAddress);
                if (is_null($invite)) {
                    return $user;
                }

                if(!is_null($invite->accepted_at)){
                    return $invite;
                }

                $accepted = $invites->where('email_address', $invite->email_address)->whereNotNull('accepted_at')->first();

                return is_null($accepted) ? $invite : $accepted;
            })
            ->filter();

        return AssociatedUserResource::collection($results);
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (ProjectInvite::whereToken($token)->exists());

        return $token;
    }
}
