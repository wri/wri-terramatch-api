<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use Illuminate\Auth\AuthManager;
use App\Validators\VersionValidator as OrganisationDocumentVersionValidator;
use App\Models\OrganisationDocument as OrganisationDocumentModel;
use App\Models\OrganisationDocumentVersion as OrganisationDocumentVersionModel;
use App\Services\VersionService;
use App\Resources\OrganisationDocumentVersionResource;
use App\Services\NotificationService;

class OrganisationDocumentVersionsController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $organisationDocumentVersionValidator = null;
    protected $authManager = null;
    protected $versionService = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        OrganisationDocumentVersionValidator $organisationDocumentVersionValidator,
        AuthManager $authManager,
        OrganisationDocumentModel $organisationDocumentModel,
        OrganisationDocumentVersionModel $organisationDocumentVersionModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->organisationDocumentVersionValidator = $organisationDocumentVersionValidator;
        $this->authManager = $authManager;
        $this->versionService = new VersionService($organisationDocumentModel, $organisationDocumentVersionModel);
        $this->notificationService = $notificationService;
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("read", $parentAndChild->child);
        $resource = new OrganisationDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllByOrganisationDocumentAction(Request $request, int $id): JsonResponse
    {
        $parentAndChildren = $this->versionService->findAllChildren($id);
        $this->authorize("read", $parentAndChildren->parent);
        $resources = [];
        foreach ($parentAndChildren->children as $child) {
            $resources[] = new OrganisationDocumentVersionResource($parentAndChildren->parent, $child);
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
        $resource = new OrganisationDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function rejectAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("approve", $parentAndChild->child);
        $data = $request->json()->all();
        $this->organisationDocumentVersionValidator->validate("reject", $data);
        $me = $this->authManager->user();
        $parentAndChild = $this->versionService->rejectChild($id, $me->id, $data["rejected_reason"]);
        $this->notificationService->notifyVersionRejected($parentAndChild->child);
        $resource = new OrganisationDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
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
