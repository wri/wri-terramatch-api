<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Organisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationRetractMyDraftController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $organisation = $user->organisation;

        if (empty($organisation)) {
            return new JsonResponse('You don\'t have an organisation.', 406);
        }

        if ($organisation->status !== Organisation::STATUS_DRAFT) {
            return new JsonResponse('Your organisation is not a draft.', 406);
        }

        $user->organisation_id = null;
        $user->save();

        $organisation->delete();

        return JsonResponseHelper::success(['Your draft organisation was removed.'], 200);
    }
}
