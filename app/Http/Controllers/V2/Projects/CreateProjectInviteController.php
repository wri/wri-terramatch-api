<?php

namespace App\Http\Controllers\V2\Projects;

use App\Exceptions\UserIsAlreadyPartOfProgrammeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Projects\CreateProjectInviteRequest;
use App\Http\Resources\V2\Projects\ProjectInviteResource;
use App\Mail\V2ProjectInviteReceived;
use App\Mail\V2ProjectMonitoringNotification;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectInvite;
use App\Models\V2\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateProjectInviteController extends Controller
{
    public function __invoke(CreateProjectInviteRequest $request, Project $project): ProjectInviteResource
    {
        $this->authorize('inviteUser', $project);

        $data = $request->validated();
        $url = data_get($data, 'callback_url');

        $existingUser = User::whereEmailAddress($data['email_address'])->first();
        if ($existingUser && $existingUser->projects->contains($project)) {
            throw new UserIsAlreadyPartOfProgrammeException();
        }

        $token = $this->generateUniqueToken();
        $data['project_id'] = $project->id;
        $data['token'] = $token;
        $organisation = Organisation::where('id', $project->organisation_id)->first();
        if ($existingUser) {
            $existingUser->projects()->sync([$project->id => ['is_monitoring' => true]], false);
            $data['accepted_at'] = now();
            $projectInvite = $project->invites()->create($data);
            if ($existingUser->organisation_id == null) {
                $existingUser->update(['organisation_id' => $project->organisation_id]);
            }
            Mail::to($data['email_address'])->queue(new V2ProjectMonitoringNotification($project->name, $url));
        } else {
            $projectInvite = $project->invites()->create($data);
            Mail::to($data['email_address'])->queue(new V2ProjectInviteReceived($project->name, $organisation->name, $url));
        }

        return new ProjectInviteResource($projectInvite);
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (ProjectInvite::whereToken($token)->first());

        return $token;
    }
}
