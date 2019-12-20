<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use Illuminate\Auth\AuthManager;
use App\Validators\VersionValidator as OrganisationVersionValidator;
use App\Models\Organisation as OrganisationModel;
use App\Models\OrganisationVersion as OrganisationVersionModel;
use App\Services\VersionService;
use App\Resources\OrganisationVersionResource;
use App\Services\NotificationService;

class OrganisationVersionsController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $organisationVersionValidator = null;
    protected $authManager = null;
    protected $versionService = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        OrganisationVersionValidator $organisationVersionValidator,
        AuthManager $authManager,
        OrganisationModel $organisationModel,
        OrganisationVersionModel $organisationVersionModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->organisationVersionValidator = $organisationVersionValidator;
        $this->authManager = $authManager;
        $this->versionService = new VersionService($organisationModel, $organisationVersionModel);
        $this->notificationService = $notificationService;
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("read", $parentAndChild->child);
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $parentAndChildren = $this->versionService->findAllChildren($id);
        $this->authorize("read", $parentAndChildren->parent);
        $resources = [];
        foreach ($parentAndChildren->children as $child) {
            $resources[] = new OrganisationVersionResource($parentAndChildren->parent, $child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function approveAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("approve", $parentAndChild->child);
        $me = $this->authManager->user();
        $parentAndChild = $this->versionService->approveChild($id, $me->id);
        $this->notificationService->notifyVersionApproved($parentAndChild->child);
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function rejectAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("approve", $parentAndChild->child);
        $data = $request->json()->all();
        $this->organisationVersionValidator->validate("reject", $data);
        $me = $this->authManager->user();
        $parentAndChild = $this->versionService->rejectChild($id, $me->id, $data["rejected_reason"]);
        $this->notificationService->notifyVersionRejected($parentAndChild->child);
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("delete", $parentAndChild->child);
        $this->versionService->deleteChild($parentAndChild->child->id);
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}

