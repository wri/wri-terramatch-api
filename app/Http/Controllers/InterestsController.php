<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateInterestException;
use App\Exceptions\InvisibleOfferException;
use App\Exceptions\InvisiblePitchException;
use App\Exceptions\MonitoringExistsException;
use App\Helpers\InterestHelper;
use App\Helpers\JsonResponseHelper;
use App\Jobs\NotifyInterestJob;
use App\Jobs\NotifyUnmatchJob;
use App\Models\Interest as InterestModel;
use App\Models\Match as MatchModel;
use App\Models\Monitoring as MonitoringModel;
use App\Models\Offer as OfferModel;
use App\Models\Pitch as PitchModel;
use App\Resources\InterestResource;
use App\Validators\InterestValidator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class InterestsController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\Interest");
        $data = $request->json()->all();
        InterestValidator::validate("CREATE", $data);
        $offer = OfferModel::findOrFail($data["offer_id"]);
        $pitch = PitchModel::findOrFail($data["pitch_id"]);
        if (in_array($offer->visibility, ["archived", "fully_invested_funded", "finished"])) {
            throw new InvisibleOfferException();
        }
        if (in_array($pitch->visibility, ["archived", "fully_invested_funded", "finished"])) {
            throw new InvisiblePitchException();
        }
        $initiator = $data["initiator"] == "offer" ? $offer : $pitch;
        $this->authorize("update", $initiator);
        $me = Auth::user();
        $data["organisation_id"] = $me->organisation_id;
        $exists = InterestModel
            ::where("organisation_id", "=", $data["organisation_id"])
            ->where("initiator", "=", $data["initiator"])
            ->where("offer_id", "=", $data["offer_id"])
            ->where("pitch_id", "=", $data["pitch_id"])
            ->first();
        if (!is_null($exists)) {
            throw new DuplicateInterestException();
        }
        $interest = new InterestModel($data);
        $interest->saveOrFail();
        $interest->refresh();
        NotifyInterestJob::dispatch($interest);
        return JsonResponseHelper::success(new InterestResource($interest), 201);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $interest = InterestModel::findOrFail($id);
        $this->authorize("delete", $interest);
        $match = MatchModel
            ::where("primary_interest_id", "=", $interest->id)
            ->orWhere("secondary_interest_id", "=", $interest->id)
            ->first();
        if (!is_null($match)) {
            $monitoring = MonitoringModel::where("match_id", "=", $match->id)->first();
            if (!is_null($monitoring)) {
                throw new MonitoringExistsException();
            }
            if ($match->primary_interest_id == $interest->id) {
                $siblingInterest = InterestModel::findOrFail($match->secondary_interest_id);
            } else {
                $siblingInterest = InterestModel::findOrFail($match->primary_interest_id);
            }
            $siblingInterest->matched = false;
            $siblingInterest->save();
            $match->delete();
            NotifyUnmatchJob::dispatch(
                OfferModel::findOrFail($interest->offer_id),
                PitchModel::findOrFail($interest->pitch_id),
                ucfirst($interest->initiator)
            );
        }
        $interest->delete();
        return JsonResponseHelper::success((object) [], 200);
    }

    public function readAllByTypeAction(Request $request, string $type): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Interest");
        $me = Auth::user();
        switch ($type) {
            case "initiated":
                $ids = InterestHelper::findInitiated($me->organisation_id);
                break;
            case "received":
                $ids = InterestHelper::findReceived($me->organisation_id);
                break;
            default:
                throw new Exception();
        }
        $interests = InterestModel
            ::whereIn("id", $ids)
            ->orderBy("created_at", "desc")
            ->get();
        $resources = [];
        foreach ($interests as $interest) {
            $resources[] = new InterestResource($interest);
        }
        return JsonResponseHelper::success($resources, 200);
    }
}
