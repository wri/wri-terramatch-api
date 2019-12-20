<?php

namespace App\Http\Controllers;

use App\Resources\InterestResource;
use App\Validators\InterestValidator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use App\Models\Interest as InterestModel;
use App\Models\Offer as OfferModel;
use App\Models\Pitch as PitchModel;
use Illuminate\Auth\AuthManager;
use App\Models\Match as MatchModel;
use App\Services\NotificationService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InterestsController extends Controller
{
    private $jsonResponseFactory = null;
    private $interestModel = null;
    private $interestValidator = null;
    private $offerModel = null;
    private $pitchModel = null;
    private $authManager = null;
    private $matchModel = null;
    private $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        InterestModel $interestModel,
        InterestValidator $interestValidator,
        OfferModel $offerModel,
        PitchModel $pitchModel,
        AuthManager $authManager,
        MatchModel $matchModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->interestModel = $interestModel;
        $this->interestValidator = $interestValidator;
        $this->offerModel = $offerModel;
        $this->pitchModel = $pitchModel;
        $this->authManager = $authManager;
        $this->matchModel = $matchModel;
        $this->notificationService = $notificationService;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\Interest");
        $data = $request->json()->all();
        $this->interestValidator->validate("create", $data);
        $offer = $this->offerModel->findOrFail($data["offer_id"]);
        $pitch = $this->pitchModel->findOrFail($data["pitch_id"]);
        if ($offer->completed || $pitch->completed) {
            return $this->jsonResponseFactory->error([], 400);
        }
        $initiator = $data["initiator"] == "offer" ? $offer : $pitch;
        $this->authorize("update", $initiator);
        $me = $this->authManager->user();
        $data["organisation_id"] = $me->organisation_id;
        $exists = $this->interestModel
            ->where("organisation_id", "=", $data["organisation_id"])
            ->where("initiator", "=", $data["initiator"])
            ->where("offer_id", "=", $data["offer_id"])
            ->where("pitch_id", "=", $data["pitch_id"])
            ->first();
        if (!is_null($exists)) {
            return $this->jsonResponseFactory->error([], 400);
        }
        $interest = $this->interestModel->newInstance($data);
        $interest->saveOrFail();
        $interest->refresh();
        $this->notificationService->notifyInterest($interest);
        return $this->jsonResponseFactory->success(new InterestResource($interest), 201);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $interest = $this->interestModel->findOrFail($id);
        $this->authorize("delete", $interest);
        $match = $this->matchModel
            ->where("primary_interest_id", "=", $interest->id)
            ->orWhere("secondary_interest_id", "=", $interest->id)
            ->first();
        if (!is_null($match)) {
            $otherId = $match->primary_interest_id == $interest->id ? $match->secondary_interest_id : $match->primary_interest_id;
            $otherInterest = $this->interestModel->findOrFail($otherId);
            $otherInterest->matched = false;
            $otherInterest->save();
            $match->delete();
        }
        $interest->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }

    public function readAllByTypeAction(Request $request, string $type): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Interest");
        $me = $this->authManager->user();
        switch ($type) {
            case "initiated":
                $interests = $this->interestModel->where("organisation_id", "=", $me->organisation_id)->get();
                break;
            case "received":
                $ids = Collection::make(
                    DB::select(
                        "SELECT `interests`.`id` FROM `interests`
                        LEFT JOIN `offers` ON `interests`.`offer_id` = `offers`.`id`
                        LEFT JOIN `pitches` ON `interests`.`pitch_id` = `pitches`.`id`
                        WHERE `interests`.`organisation_id` != ?
                        AND (`offers`.`organisation_id` = ? OR `pitches`.`organisation_id` = ?);",
                        [$me->organisation_id, $me->organisation_id, $me->organisation_id]
                    )
                )->pluck("id")->toArray();
                $interests = $this->interestModel->whereIn("id", $ids)->get();
                break;
            default:
                throw new Exception();
        }
        $resources = [];
        foreach ($interests as $interest) {
            $resources[] = new InterestResource($interest);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }
}
