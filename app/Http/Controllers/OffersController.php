<?php

namespace App\Http\Controllers;

use App\Jobs\RecordFilters;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use App\Validators\OfferValidator;
use Illuminate\Auth\AuthManager;
use App\Models\Offer as OfferModel;
use App\Models\Organisation as OrganisationModel;
use App\Resources\OfferResource;
use App\Models\Upload as UploadModel;
use App\Services\SearchService;
use DateTime;
use Illuminate\Support\Facades\Auth;

class OffersController extends Controller
{
    private $jsonResponseFactory = null;
    private $offerValidator = null;
    private $authManager = null;
    private $offerModel = null;
    private $organisationModel = null;
    private $uploadModel = null;
    protected $searchService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        OfferValidator $offerValidator,
        AuthManager $authManager,
        OfferModel $offerModel,
        OrganisationModel $organisationModel,
        UploadModel $uploadModel,
        SearchService $searchService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->offerValidator = $offerValidator;
        $this->authManager = $authManager;
        $this->offerModel = $offerModel;
        $this->organisationModel = $organisationModel;
        $this->uploadModel = $uploadModel;
        $this->searchService = $searchService;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\Offer");
        $data = $request->json()->all();
        $this->offerValidator->validate("create", $data);
        $me = $this->authManager->user();
        $data["cover_photo"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
            $data["cover_photo"], UploadModel::IMAGES, $me->id
        );
        $data["avatar"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
            $data["avatar"], UploadModel::IMAGES, $me->id
        );
        $data["video"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
            $data["video"], UploadModel::VIDEOS, $me->id
        );
        $this->uploadModel->assertUnique($data["cover_photo"], $data["avatar"], $data["video"]);
        $data["organisation_id"] = $me->organisation_id;
        $offer = $this->offerModel->newInstance($data);
        $offer->saveOrFail();
        $offer->refresh();
        return $this->jsonResponseFactory->success(new OfferResource($offer), 201);
    }

    public function readAllByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = $this->organisationModel->findOrFail($id);
        $this->authorize("read", $organisation);
        $offers = $this->offerModel->where("organisation_id", "=", $organisation->id)->get();
        $resources = [];
        foreach ($offers as $offer) {
            $resources[] = new OfferResource($offer);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $offer = $this->offerModel->findOrFail($id);
        $this->authorize("read", $offer);
        return $this->jsonResponseFactory->success(new OfferResource($offer), 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $offer = $this->offerModel->findOrFail($id);
        $this->authorize("update", $offer);
        $data = $request->json()->all();
        $this->offerValidator->validate("update", $data);
        $me = $this->authManager->user();
        if (array_key_exists("cover_photo", $data)) {
            $data["cover_photo"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $data["cover_photo"], UploadModel::IMAGES, $me->id
            );
        }
        if (array_key_exists("avatar", $data)) {
            $data["avatar"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $data["avatar"], UploadModel::IMAGES, $me->id
            );
        }
        if (array_key_exists("video", $data)) {
            $data["video"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $data["video"], UploadModel::VIDEOS, $me->id
            );
        }
        $this->uploadModel->assertUnique(@$data["cover_photo"], @$data["avatar"], @$data["video"]);
        $offer->fill($data);
        $offer->saveOrFail();
        return $this->jsonResponseFactory->success(new OfferResource($offer), 200);
    }

    public function searchAction(Request $request): JsonResponse
    {
        $this->authorize("search", "App\\Models\\Offer");
        RecordFilters::dispatchNow(Auth::user(), 'offers', $request->get('filters'));
        $conditions = $this->searchService->parse($request);
        $offers = $this->offerModel->search($conditions)->get();
        $resources = [];
        foreach ($offers as $offer) {
            $resources[] = new OfferResource($offer, true);
        }
        $meta = $this->searchService->summarise("Offer", $resources, $conditions);
        return $this->jsonResponseFactory->success($resources, 200, $meta);
    }

    public function completeAction(Request $request, int $id): JsonResponse
    {
        $offer = $this->offerModel->findOrFail($id);
        $this->authorize("update", $offer);
        $data = $request->json()->all();
        $this->offerValidator->validate("complete", $data);
        $me = $this->authManager->user();
        $offer->completed = true;
        $offer->successful = $data["successful"];
        $offer->completed_by = $me->id;
        $offer->completed_at = new DateTime();
        $offer->saveOrFail();
        return $this->jsonResponseFactory->success(new OfferResource($offer), 200);
    }
}
