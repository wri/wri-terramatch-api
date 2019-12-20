<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use Illuminate\Auth\AuthManager;
use App\Validators\VersionValidator as PitchDocumentVersionValidator;
use App\Models\PitchDocument as PitchDocumentModel;
use App\Models\PitchDocumentVersion as PitchDocumentVersionModel;
use App\Services\VersionService;
use App\Resources\PitchDocumentVersionResource;
use App\Services\NotificationService;

class PitchDocumentVersionsController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $pitchDocumentVersionValidator = null;
    protected $authManager = null;
    protected $versionService = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        PitchDocumentVersionValidator $pitchDocumentVersionValidator,
        AuthManager $authManager,
        PitchDocumentModel $pitchDocumentModel,
        PitchDocumentVersionModel $pitchDocumentVersionModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->pitchDocumentVersionValidator = $pitchDocumentVersionValidator;
        $this->authManager = $authManager;
        $this->versionService = new VersionService($pitchDocumentModel, $pitchDocumentVersionModel);
        $this->notificationService = $notificationService;
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("read", $parentAndChild->child);
        $resource = new PitchDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllByPitchDocumentAction(Request $request, int $id): JsonResponse
    {
        $parentAndChildren = $this->versionService->findAllChildren($id);
        $this->authorize("read", $parentAndChildren->parent);
        $resources = [];
        foreach ($parentAndChildren->children as $child) {
            $resources[] = new PitchDocumentVersionResource($parentAndChildren->parent, $child);
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
        $resource = new PitchDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function rejectAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("approve", $parentAndChild->child);
        $data = $request->json()->all();
        $this->pitchDocumentVersionValidator->validate("reject", $data);
        $me = $this->authManager->user();
        $parentAndChild = $this->versionService->rejectChild($id, $me->id, $data["rejected_reason"]);
        $this->notificationService->notifyVersionRejected($parentAndChild->child);
        $resource = new PitchDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
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
