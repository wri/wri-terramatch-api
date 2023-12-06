<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Events\V2\Organisation\OrganisationUserRequestApprovedEvent;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Organisations\ApproveRejectUserRequest;
use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Http\JsonResponse;

class OrganisationApproveUserController extends Controller
{
    public function __invoke(ApproveRejectUserRequest $request): JsonResponse
    {
        $organisation = Organisation::where('uuid', $request->get('organisation_uuid'))->firstOrFail();
        $user = User::where('uuid', $request->get('user_uuid'))->firstOrFail();

        $this->authorize('approveRejectUser', $organisation);

        $organisation->partners()->updateExistingPivot($user, ['status' => 'approved'], false);

        OrganisationUserRequestApprovedEvent::dispatch($request->user(), $user, $organisation);

        return JsonResponseHelper::success(['User successfully approved.'], 200);
    }
}
