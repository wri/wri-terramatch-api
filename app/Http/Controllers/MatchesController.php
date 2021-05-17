<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\MatchHelper;
use App\Models\Match as MatchModel;
use App\Resources\MatchResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatchesController extends Controller
{
    public function readAction(Request $request, int $id): JsonResponse
    {
        $match = MatchModel::findOrFail($id);
        $this->authorize("read", $match);
        $match = MatchHelper::findMatch($match->id);
        $offerContacts = MatchHelper::findOfferContacts($match->offer_id);
        $pitchContacts = MatchHelper::findPitchContacts($match->pitch_id);
        return JsonResponseHelper::success(new MatchResource($match, $offerContacts, $pitchContacts), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Match");
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
