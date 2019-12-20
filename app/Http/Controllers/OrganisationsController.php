<?php

namespace App\Http\Controllers;

use App\Http\JsonResponseFactory;
use App\Models\Upload;
use App\Resources\OrganisationResource;
use App\Resources\OrganisationVersionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Validators\OrganisationValidator;
use Illuminate\Auth\AuthManager;
use App\Models\Organisation as OrganisationModel;
use App\Models\OrganisationVersion as OrganisationVersionModel;
use App\Models\Upload as UploadModel;
use App\Services\VersionService;
use App\Services\NotificationService;

class OrganisationsController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $authManager = null;
    protected $organisationValidator = null;
    protected $uploadModel = null;
    protected $versionService = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        AuthManager $authManager,
        OrganisationValidator $organisationValidator,
        OrganisationModel $organisationModel,
        OrganisationVersionModel $organisationVersionModel,
        UploadModel $uploadModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->organisationValidator = $organisationValidator;
        $this->authManager = $authManager;
        $this->uploadModel = $uploadModel;
        $this->versionService = new VersionService($organisationModel, $organisationVersionModel);
        $this->notificationService = $notificationService;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\Organisation");
        $childData = $request->json()->all();
        $this->organisationValidator->validate("create", $childData);
        $me = $this->authManager->user();
        $parentData = [];
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
        $me->organisation_id = $parentAndChild->parent->id;
        $me->saveOrFail();
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("read", $parentAndChild->parent);
        $resource = new OrganisationResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("update", $parentAndChild->parent);
        $childData = $request->json()->all();
        $this->organisationValidator->validate("update", $childData);
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
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Organisation");
        $parentsAndChildren = $this->versionService->findAllParents();
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new OrganisationResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }
}
