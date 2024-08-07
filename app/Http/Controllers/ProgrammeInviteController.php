<?php

namespace App\Http\Controllers;

use App\Exceptions\InviteAlreadyAcceptedException;
use App\Exceptions\UserIsAlreadyPartOfProgrammeException;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\AcceptProgrammeInviteRequest;
use App\Http\Requests\RemoveProgrammeInviteRequest;
use App\Http\Requests\StoreProgrammeInviteRequest;
use App\Mail\ProgrammeInviteReceived;
use App\Mail\UserInvited;
use App\Models\Programme;
use App\Models\ProgrammeInvite;
use App\Models\V2\User;
use App\Resources\ProgrammeInviteResource;
use App\Resources\UserResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProgrammeInviteController extends Controller
{
    public function createAction(StoreProgrammeInviteRequest $request, Programme $programme): JsonResponse
    {
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $request->request->remove('callback_url');
        $this->authorize('invite', $programme);
        $data = $request->json()->all();

        $existingUser = User::where('email_address', $data['email_address'])->first();
        if ($existingUser && $existingUser->programmes->contains($programme)) {
            throw new UserIsAlreadyPartOfProgrammeException();
        }

        do {
            $token = Str::random(64);
        } while (ProgrammeInvite::where('token', $token)->first());

        $extra = [
            'programme_id' => $programme->id,
            'token' => $token,
        ];

        $programmeInvite = ProgrammeInvite::create(array_merge($data, $extra));

        if (! $existingUser) {
            $user = User::create($data);

            assignSpatieRole($user);

            // Automatically accept the invite
            $user->programmes()->sync([$programme->id => ['is_monitoring' => true]]);

            $programmeInvite->update(['accepted_at' => now()]);

            // Send the user an invite to join Terramatch
            Mail::to($user->email_address)->queue(new UserInvited($user->email_address, 'User', $url));
        } else {
            Mail::to($data['email_address'])->queue(new ProgrammeInviteReceived($programme->name, $programmeInvite->token, $url));
        }

        return JsonResponseHelper::success(new ProgrammeInviteResource($programmeInvite), 201);
    }

    public function acceptAction(AcceptProgrammeInviteRequest $request): JsonResponse
    {
        $this->authorize('accept', Programme::class);
        $data = $request->json()->all();

        $invite = ProgrammeInvite::where('token', $request->get('token'))
            ->where('email_address', Auth::user()->email_address)
            ->first();

        if (! $invite) {
            throw new ModelNotFoundException();
        } elseif ($invite['accepted_at'] !== null) {
            throw new InviteAlreadyAcceptedException();
        }

        Auth::user()->programmes()->sync([$invite->programme_id => ['is_monitoring' => true]], false);

        $invite->accepted_at = now();
        $invite->saveOrFail();

        return JsonResponseHelper::success(new ProgrammeInviteResource($invite), 200);
    }

    public function deleteAction(ProgrammeInvite $programmeInvite): JsonResponse
    {
        $this->authorize('delete', $programmeInvite);

        $programmeInvite->delete();

        return JsonResponseHelper::success((object) [], 200);
    }

    public function readAllAction(Request $request, Programme $programme): JsonResponse
    {
        $this->authorize('readAllPartners', $programme);

        $partners = $programme->users()->where('is_monitoring', '=', true)->get();
        $resources = [];

        foreach ($partners as $partner) {
            $resources[] = new UserResource($partner);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function removeUserAction(RemoveProgrammeInviteRequest $request): JsonResponse
    {
        $this->authorize('removeUser', Programme::class);
        $data = $request->json()->all();

        $programmeId = $data['programme_id'];
        $user = User::user()
            ->where('id', '=', $data['user_id'])
            ->firstOrFail();
        $user->programmes()->detach([$programmeId]);

        return JsonResponseHelper::success((object) [], 200);
    }
}
