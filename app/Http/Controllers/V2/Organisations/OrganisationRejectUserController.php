<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Events\V2\Organisation\OrganisationUserRequestRejectedEvent;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Organisations\ApproveRejectUserRequest;
use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Http\JsonResponse;

class OrganisationRejectUserController extends Controller
{
    public function __invoke(ApproveRejectUserRequest $request): JsonResponse
    {
        $organisation = Organisation::where('uuid', $request->get('organisation_uuid'))->firstOrFail();
        $user = User::where('uuid', $request->get('user_uuid'))->firstOrFail();

        $this->authorize('approveRejectUser', $organisation);

        $organisation->partners()->updateExistingPivot($user, ['status' => 'rejected'], false);

        OrganisationUserRequestRejectedEvent::dispatch($request->user(), $user, $organisation);

        return JsonResponseHelper::success(['User request successfully rejected.'], 200);
    }
}
