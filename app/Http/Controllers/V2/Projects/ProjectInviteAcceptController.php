<?php

namespace App\Http\Controllers\V2\Projects;

use App\Exceptions\InviteAlreadyAcceptedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Projects\ProjectInviteAcceptRequest;
use App\Http\Resources\V2\Projects\ProjectInviteResource;
use App\Models\V2\Projects\ProjectInvite;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class ProjectInviteAcceptController extends Controller
{
    public function __invoke(ProjectInviteAcceptRequest $request): ProjectInviteResource
    {
        $data = $request->validated();

        $invite = ProjectInvite::where('token', $request->get('token'))
            ->where('email_address', Auth::user()->email_address)
            ->first();

        if (! $invite) {
            throw new ModelNotFoundException();
        } elseif ($invite['accepted_at'] !== null) {
            throw new InviteAlreadyAcceptedException();
        }

        Auth::user()->projects()->sync([$invite->project_id => ['is_monitoring' => true]], false);

        $invite->accepted_at = now();
        $invite->saveOrFail();

        return new ProjectInviteResource($invite);
    }
}
