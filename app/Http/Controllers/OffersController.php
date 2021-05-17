<?php

namespace App\Http\Controllers;

use App\Exceptions\MonitoringExistsException;
use App\Helpers\JsonResponseHelper;
use App\Helpers\MonitoringHelper;
use App\Helpers\UploadHelper;
use App\Jobs\CreateFilterRecordJob;
use App\Jobs\NotifyProjectUpdatedJob;
use App\Models\Offer as OfferModel;
use App\Models\Organisation as OrganisationModel;
use App\Resources\OfferResource;
use App\Validators\OfferValidator;
use DateTime;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class OffersController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\Offer");
        $data = $request->json()->all();
        OfferValidator::validate("CREATE", $data);
        $me = Auth::user();
        $data["cover_photo"] = UploadHelper::findByIdAndValidate(
            $data["cover_photo"], UploadHelper::IMAGES, $me->id
        );
        $data["video"] = UploadHelper::findByIdAndValidate(
            $data["video"], UploadHelper::VIDEOS, $me->id
        );
        UploadHelper::assertUnique($data["cover_photo"], $data["video"]);
        $data["organisation_id"] = $me->organisation_id;
        $data["visibility_updated_at"] = new DateTime("now", new DateTimeZone("UTC"));
        $offer = new OfferModel($data);
        $offer->saveOrFail();
        $offer->refresh();
        return JsonResponseHelper::success(new OfferResource($offer), 201);
    }

    public function readAllByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = OrganisationModel::findOrFail($id);
        $this->authorize("read", $organisation);
        $offers = OfferModel
            ::where("organisation_id", "=", $organisation->id)
            ->whereNotIn("visibility", ["archived", "finished"])
            ->orderBy("name", "asc")
            ->get();
        $resources = [];
        foreach ($offers as $offer) {
            $resources[] = new OfferResource($offer);
        }
        return JsonResponseHelper::success($resources, 200);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $offer = OfferModel::findOrFail($id);
        $this->authorize("read", $offer);
        return JsonResponseHelper::success(new OfferResource($offer), 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $offer = OfferModel::findOrFail($id);
        $this->authorize("update", $offer);
        $data = $request->json()->all();
        OfferValidator::validate("UPDATE", $data);
        $me = Auth::user();
        if (array_key_exists("cover_photo", $data)) {
            $data["cover_photo"] = UploadHelper::findByIdAndValidate(
                $data["cover_photo"], UploadHelper::IMAGES, $me->id
            );
        }
        if (array_key_exists("video", $data)) {
            $data["video"] = UploadHelper::findByIdAndValidate(
                $data["video"], UploadHelper::VIDEOS, $me->id
            );
        }
        UploadHelper::assertUnique(@$data["cover_photo"], @$data["video"]);
        $offer->fill($data);
        $offer->saveOrFail();
        NotifyProjectUpdatedJob::dispatch($offer);
        return JsonResponseHelper::success(new OfferResource($offer), 200);
    }

    public function searchAction(Request $request): JsonResponse
    {
        $searchService = App::make("App\\Services\\Search\\SearchService");
        $this->authorize("search", "App\\Models\\Offer");
        $conditions = $searchService->parse($request);
        $offers = OfferModel::search($conditions, Auth::user()->organisation_id)->get();
        CreateFilterRecordJob::dispatch(Auth::user(), 'offers', $conditions);
        $resources = [];
        foreach ($offers as $offer) {
            $resources[] = new OfferResource($offer, true);
        }
        $meta = $searchService->summarise("Offer", $resources, $conditions);
        return JsonResponseHelper::success($resources, 200, $meta);
    }

    public function updateVisibilityAction(Request $request, int $id): JsonResponse
    {
        $offer = OfferModel::findOrFail($id);
        $this->authorize("updateVisibility", $offer);
        $data = $request->json()->all();
        OfferValidator::validate("UPDATE_VISIBILITY", $data);
        if (!MonitoringHelper::isNewVisibilityValid($offer, $data["visibility"])) {
            throw new MonitoringExistsException();
        }
        $offer->visibility = $data["visibility"];
        $offer->visibility_updated_at = new DateTime("now", new DateTimeZone("UTC"));
        $offer->saveOrFail();
        MonitoringHelper::progressRelatedMonitoringStages($offer, $data["visibility"]);
        return JsonResponseHelper::success(new OfferResource($offer), 200);
    }

    public function inspectByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = OrganisationModel::findOrFail($id);
        $this->authorize("inspect", $organisation);
        $offers = OfferModel
            ::where("organisation_id", "=", $organisation->id)
            ->orderBy("name", "asc")
            ->get();
        $resources = [];
        foreach ($offers as $offer) {
            $resources[] = new OfferResource($offer);
        }
        return JsonResponseHelper::success($resources, 200);
    }

    public function mostRecentAction(Request $request): JsonResponse
    {
        $this->authorize("search", "App\\Models\\Offer");
        $request->validate(['limit' => 'integer']);
        $limit = (int) $request->get('limit', 10);
        $offers =  OfferModel::orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
        $resources = $offers->map(function($offer){
            return new OfferResource($offer);
        });
        return JsonResponseHelper::success($resources, 200);
    }
}

