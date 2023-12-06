<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Events\V2\Organisation\OrganisationUserJoinRequestEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Organisations\JoinExistingOrganisationRequest;
use App\Http\Resources\V2\General\MessageResource;
use App\Models\V2\Organisation;

class JoinExistingOrganisationController extends Controller
{
    public function __invoke(JoinExistingOrganisationRequest $request): MessageResource
    {
        $this->authorize('requestJoinExisting', Organisation::class);

        $organisation = Organisation::where('uuid', $request->get('organisation_uuid'))->firstOrFail();

        $request->user()->organisations()->syncWithoutDetaching([
            $organisation->id => ['status' => 'requested'],
        ]);

        OrganisationUserJoinRequestEvent::dispatch($request->user(), $organisation);

        return new MessageResource(['message' => 'Request successfully submitted.']);
    }
}
