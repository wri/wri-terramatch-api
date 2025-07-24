<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Exceptions\UserIsAlreadyPartOfProgrammeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Organisations\CreateOrganisationInviteRequest;
use App\Http\Resources\V2\Organisation\OrganisationInviteResource;
use App\Mail\V2OrganisationInviteReceived;
use App\Models\V2\Organisation;
use App\Models\V2\Organisations\OrganisationInvite;
use App\Models\V2\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateOrganisationInviteController extends Controller
{
    private const PASSWORD_KEYSPACE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';

    public function __invoke(CreateOrganisationInviteRequest $request, Organisation $organisation): OrganisationInviteResource
    {
        $this->authorize('inviteUser', $organisation);

        $data = $request->validated();
        $url = data_get($data, 'callback_url');

        $existingUser = User::whereEmailAddress($data['email_address'])->first();

        $token = $this->generateUniqueToken();
        $data['organisation_id'] = $organisation->id;
        $data['token'] = $token;
        if ($existingUser) {
            throw new UserIsAlreadyPartOfProgrammeException();
        } else {
            $user = User::create([
                'email_address' => $data['email_address'],
                'organisation_id' => $organisation->id,
                'password' => $this->generateRandomPassword(),
            ]);
            $user->assignRole('project-developer');
            $user->refresh();

            $organisation = Organisation::where('id', $organisation->id)->first();
            $organisationInvite = $organisation->invites()->create($data);

            Mail::to($data['email_address'])->queue(new V2OrganisationInviteReceived($organisation->name, $url, $user));
        }

        return new OrganisationInviteResource($organisationInvite);
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (OrganisationInvite::whereToken($token)->exists());

        return $token;
    }

    private function generateRandomPassword(): string
    {
        $pieces = [];
        $max = mb_strlen(self::PASSWORD_KEYSPACE, '8bit') - 1;
        for ($i = 0; $i < 64; $i++) {
            $pieces [] = self::PASSWORD_KEYSPACE[random_int(0, $max)];
        }

        return implode('', $pieces);
    }
}
