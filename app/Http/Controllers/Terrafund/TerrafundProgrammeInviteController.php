<?php

namespace App\Http\Controllers\Terrafund;

use App\Exceptions\UserIsAlreadyPartOfProgrammeException;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\AcceptTerrafundProgrammeInviteRequest;
use App\Http\Requests\Terrafund\StoreTerrafundProgrammeInviteRequest;
use App\Mail\TerrafundProgrammeInviteReceived;
use App\Mail\UserInvited;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeInvite;
use App\Models\V2\User;
use App\Resources\Terrafund\TerrafundProgrammeInviteResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TerrafundProgrammeInviteController extends Controller
{
    public function createAction(StoreTerrafundProgrammeInviteRequest $request, TerrafundProgramme $terrafundProgramme): JsonResponse
    {
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $request->request->remove('callback_url');
        $this->authorize('invite', $terrafundProgramme);
        $data = $request->json()->all();

        $existingUser = User::where('email_address', $data['email_address'])->first();
        if ($existingUser && $existingUser->terrafundProgrammes->contains($terrafundProgramme)) {
            throw new UserIsAlreadyPartOfProgrammeException();
        }

        if (! $existingUser) {
            $user = User::create($data);

            assignSpatieRole($user);

            // Automatically accept the invite
            $user->terrafundProgrammes()->sync([$terrafundProgramme->id]);

            // Send the user an invite to join Terramatch
            Mail::to($user->email_address)->queue(new UserInvited($user->email_address, 'User', $url));

            return JsonResponseHelper::success([], 201);
        }

        $programmeInvite = new TerrafundProgrammeInvite();
        $programmeInvite->email_address = $data['email_address'];
        $programmeInvite->terrafund_programme_id = $terrafundProgramme->id;
        do {
            $programmeInvite->token = Str::random(64);
        } while (TerrafundProgrammeInvite::where('token', $programmeInvite->token)->first());
        $programmeInvite->saveOrFail();

        Mail::to($data['email_address'])->queue(new TerrafundProgrammeInviteReceived($terrafundProgramme->name, $programmeInvite->token, $url));

        return JsonResponseHelper::success(new TerrafundProgrammeInviteResource($programmeInvite), 201);
    }

    public function acceptAction(AcceptTerrafundProgrammeInviteRequest $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $data = $request->json()->all();

        $invite = TerrafundProgrammeInvite::where('token', $request->get('token'))
            ->where('email_address', Auth::user()->email_address)
            ->first();

        if (! $invite) {
            throw new ModelNotFoundException();
        }

        Auth::user()->terrafundProgrammes()->sync([$invite->terrafund_programme_id], false);

        $invite->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
