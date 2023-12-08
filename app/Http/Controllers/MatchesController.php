<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\MatchHelper;
use App\Models\Matched as MatchModel;
use App\Resources\MatchResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatchesController extends Controller
{
    public function readAction(MatchModel $matched): JsonResponse
    {
        $this->authorize('read', $matched);
        $matched = MatchHelper::findMatch($matched->id);
        $offerContacts = MatchHelper::findOfferContacts($matched->offer_id);
        $pitchContacts = MatchHelper::findPitchContacts($matched->pitch_id);

        return JsonResponseHelper::success(new MatchResource($matched, $offerContacts, $pitchContacts), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', \App\Models\Matched::class);
        $me = Auth::user();
        $matches = MatchHelper::findMatchesByOrganisation($me->organisation_id);
        $resources = [];
        foreach ($matches as $match) {
            $offerContacts = MatchHelper::findOfferContacts($match->offer_id);
            $pitchContacts = MatchHelper::findPitchContacts($match->pitch_id);
            $resources[] = new MatchResource($match, $offerContacts, $pitchContacts);
        }

        return JsonResponseHelper::success($resources, 200);
    }
}
