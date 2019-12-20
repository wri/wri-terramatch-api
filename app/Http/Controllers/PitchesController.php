<?php

namespace App\Http\Controllers;

use App\Http\JsonResponseFactory;
use App\Jobs\RecordFilters;
use App\Models\PitchVersion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Validators\PitchValidator;
use Illuminate\Auth\AuthManager;
use App\Models\Pitch as PitchModel;
use App\Models\PitchVersion as PitchVersionModel;
use App\Models\Organisation as OrganisationModel;
use App\Services\FileService;
use App\Models\Upload as UploadModel;
use App\Services\VersionService;
use App\Resources\PitchResource;
use App\Resources\PitchVersionResource;
use App\Services\SearchService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class PitchesController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $pitchValidator = null;
    protected $authManager = null;
    protected $fileService = null;
    protected $uploadModel = null;
    protected $pitchModel = null;
    protected $versionService = null;
    protected $organisationModel = null;
    protected $searchService = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        PitchValidator $pitchValidator,
        AuthManager $authManager,
        FileService $fileService,
        UploadModel $uploadModel,
        PitchModel $pitchModel,
        PitchVersionModel $pitchVersionModel,
        OrganisationModel $organisationModel,
        SearchService $searchService,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->pitchValidator = $pitchValidator;
        $this->authManager = $authManager;
        $this->fileService = $fileService;
        $this->uploadModel = $uploadModel;
        $this->pitchModel = $pitchModel;
        $this->versionService = new VersionService($pitchModel, $pitchVersionModel);
        $this->organisationModel = $organisationModel;
        $this->searchService = $searchService;
        $this->notificationService = $notificationService;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\Pitch");
        $childData = $request->json()->all();
        $this->pitchValidator->validate("create", $childData);
        $me = $this->authManager->user();
        $parentData = [
            "organisation_id" => $me->organisation_id
        ];
        $childData["cover_photo"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
            $childData["cover_photo"], UploadModel::IMAGES, $me->id
        );
        $childData["avatar"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
            $childData["avatar"], UploadModel::IMAGES, $me->id
        );
        $childData["video"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
            $childData["video"], UploadModel::VIDEOS, $me->id
        );
        $this->uploadModel->assertUnique($childData["cover_photo"], $childData["avatar"], $childData["video"]);
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new PitchVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("read", $parentAndChild->parent);
        $resource = new PitchResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("update", $parentAndChild->parent);
        $childData = $request->json()->all();
        $this->pitchValidator->validate("update", $childData);
        $me = $this->authManager->user();
        if (array_key_exists("cover_photo", $childData)) {
            $childData["cover_photo"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $childData["cover_photo"], UploadModel::IMAGES, $me->id
            );
        }
        if (array_key_exists("avatar", $childData)) {
            $childData["avatar"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $childData["avatar"], UploadModel::IMAGES, $me->id
            );
        }
        if (array_key_exists("video", $childData)) {
            $childData["video"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $childData["video"], UploadModel::VIDEOS, $me->id
            );
        }
        $this->uploadModel->assertUnique(@$childData["cover_photo"], @$childData["avatar"], @$childData["video"]);
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new PitchVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = $this->organisationModel->findOrFail($id);
        $this->authorize("read", $organisation);
        $parentsAndChildren = $this->versionService->findAllParents([
            ["organisation_id", "=", $organisation->id]
        ]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new PitchResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function searchAction(Request $request): JsonResponse
    {
        $this->authorize("search", "App\\Models\\Pitch");
        RecordFilters::dispatchNow(Auth::user(), 'pitches', $request->get('filters'));
        $conditions = $this->searchService->parse($request);
        $parentsAndChildren = $this->versionService->searchAllApprovedChildren($conditions);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new PitchResource($parentAndChild->parent, $parentAndChild->child, true);
        }
        $meta = $this->searchService->summarise("Pitch", $resources, $conditions);
        return $this->jsonResponseFactory->success($resources, 200, $meta);
    }

    public function inspectByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = $this->organisationModel->findOrFail($id);
        $this->authorize("update", $organisation);
        $parentsAndChildren = $this->versionService->groupAllChildren([["organisation_id", "=", $organisation->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new PitchVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function completeAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("update", $parentAndChild->parent);
        $parentData = $request->json()->all();
        $this->pitchValidator->validate("complete", $parentData);
        $me = $this->authManager->user();
        $parentAndChild = $this->versionService->completeParent($parentAndChild->parent->id, $me->id, $parentData["successful"]);
        $resource = new PitchResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }
}
