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

        $projectUsersEmails = $project->users()->wherePivot('is_monitoring', true)->pluck('email_address');

        foreach ($projectUsersEmails as $email_address) {
            $invite = $invites->where('email_address', '==', $email_address)->first();
            if (!$invite) {
                $token = $this->generateUniqueToken();
                $data['project_id'] = $project->id;
                $data['token'] = $token;
                $data['email_address'] = $email_address;
                $newInvite = $project->invites()->create($data);
                $invites->push($newInvite);
            }
        }

        $uniques = $invites->unique('email_address');

        $results = $uniques
            ->filter(function ($invite) use ($projectUsersEmails){
                $user = User::where('email_address', $invite->email_address)->first();
                return $user?->primaryRole?->name != 'project-manager';
            })
            ->map(function ($invite) use ($invites){
                if(!is_null($invite->accepted_at)){
                    return $invite;
            }

            $accepted = $invites->where('email_address', $invite->email_address)->whereNotNull('accepted_at')->first();

            return is_null($accepted) ? $invite : $accepted;
        });

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
