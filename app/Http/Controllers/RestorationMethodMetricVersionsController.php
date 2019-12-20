<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use Illuminate\Auth\AuthManager;
use App\Validators\VersionValidator as RestorationMethodMetricVersionValidator;
use App\Models\RestorationMethodMetric as RestorationMethodMetricModel;
use App\Models\RestorationMethodMetricVersion as RestorationMethodMetricVersionModel;
use App\Services\VersionService;
use App\Resources\RestorationMethodMetricVersionResource;
use App\Services\NotificationService;

class RestorationMethodMetricVersionsController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $restorationMethodMetricVersionValidator = null;
    protected $authManager = null;
    protected $versionService = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        RestorationMethodMetricVersionValidator $restorationMethodMetricVersionValidator,
        AuthManager $authManager,
        RestorationMethodMetricModel $restorationMethodMetricModel,
        RestorationMethodMetricVersionModel $restorationMethodMetricVersionModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->restorationMethodMetricVersionValidator = $restorationMethodMetricVersionValidator;
        $this->authManager = $authManager;
        $this->versionService = new VersionService($restorationMethodMetricModel, $restorationMethodMetricVersionModel);
        $this->notificationService = $notificationService;
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("read", $parentAndChild->child);
        $resource = new RestorationMethodMetricVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllByRestorationMethodMetricAction(Request $request, int $id): JsonResponse
    {
        $parentAndChildren = $this->versionService->findAllChildren($id);
        $this->authorize("read", $parentAndChildren->parent);
        $resources = [];
        foreach ($parentAndChildren->children as $child) {
            $resources[] = new RestorationMethodMetricVersionResource($parentAndChildren->parent, $child);
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
        $resource = new RestorationMethodMetricVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function rejectAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("approve", $parentAndChild->child);
        $data = $request->json()->all();
        $this->restorationMethodMetricVersionValidator->validate("reject", $data);
        $me = $this->authManager->user();
        $parentAndChild = $this->versionService->rejectChild($id, $me->id, $data["rejected_reason"]);
        $this->notificationService->notifyVersionRejected($parentAndChild->child);
        $resource = new RestorationMethodMetricVersionResource($parentAndChild->parent, $parentAndChild->child);
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
