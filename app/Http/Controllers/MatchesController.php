<?php

namespace App\Http\Controllers;

use App\Resources\MatchResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use Illuminate\Auth\AuthManager;
use App\Services\MatchService;
use App\Models\Match as MatchModel;

class MatchesController extends Controller
{
    private $jsonResponseFactory = null;
    private $authManager = null;
    private $matchService = null;
    private $matchModel = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        AuthManager $authManager,
        MatchService $matchService,
        MatchModel $matchModel
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->authManager = $authManager;
        $this->matchService = $matchService;
        $this->matchModel = $matchModel;
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $match = $this->matchModel->findOrFail($id);
        $this->authorize("read", $match);
        $match = $this->matchService->findMatch($match->id);
        $offerContacts = $this->matchService->findOfferContacts($match->offer_id);
        $pitchContacts = $this->matchService->findPitchContacts($match->pitch_id);
        return $this->jsonResponseFactory->success(new MatchResource($match, $offerContacts, $pitchContacts), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Match");
        $me = $this->authManager->user();
        $matches = $this->matchService->findMatchesByOrganisation($me->organisation_id);
        $resources = [];
        foreach ($matches as $match) {
            $offerContacts = $this->matchService->findOfferContacts($match->offer_id);
            $pitchContacts = $this->matchService->findPitchContacts($match->pitch_id);
            $resources[] = new MatchResource($match, $offerContacts, $pitchContacts);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }
}
