<?php

namespace App\Http\Controllers;

use App\Jobs\UpdatePricePerTreeJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use Illuminate\Auth\AuthManager;
use App\Validators\VersionValidator as TreeSpeciesVersionValidator;
use App\Models\TreeSpecies as TreeSpeciesModel;
use App\Models\TreeSpeciesVersion as TreeSpeciesVersionModel;
use App\Services\VersionService;
use App\Resources\TreeSpeciesVersionResource;
use App\Services\NotificationService;

class TreeSpeciesVersionsController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $treeSpeciesVersionValidator = null;
    protected $authManager = null;
    protected $versionService = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        TreeSpeciesVersionValidator $treeSpeciesVersionValidator,
        AuthManager $authManager,
        TreeSpeciesModel $treeSpeciesModel,
        TreeSpeciesVersionModel $treeSpeciesVersionModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->treeSpeciesVersionValidator = $treeSpeciesVersionValidator;
        $this->authManager = $authManager;
        $this->versionService = new VersionService($treeSpeciesModel, $treeSpeciesVersionModel);
        $this->notificationService = $notificationService;
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("read", $parentAndChild->child);
        $resource = new TreeSpeciesVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllByTreeSpeciesAction(Request $request, int $id): JsonResponse
    {
        $parentAndChildren = $this->versionService->findAllChildren($id);
        $this->authorize("read", $parentAndChildren->parent);
        $resources = [];
        foreach ($parentAndChildren->children as $child) {
            $resources[] = new TreeSpeciesVersionResource($parentAndChildren->parent, $child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function approveAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("approve", $parentAndChild->child);
        $me = $this->authManager->user();
        $parentAndChild = $this->versionService->approveChild($id, $me->id);
        UpdatePricePerTreeJob::dispatch($parentAndChild->parent->pitch_id);
        $this->notificationService->notifyVersionApproved($parentAndChild->child);
        $resource = new TreeSpeciesVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function rejectAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize("approve", $parentAndChild->child);
        $data = $request->json()->all();
        $this->treeSpeciesVersionValidator->validate("reject", $data);
        $me = $this->authManager->user();
        $parentAndChild = $this->versionService->rejectChild($id, $me->id, $data["rejected_reason"]);
        $this->notificationService->notifyVersionRejected($parentAndChild->child);
        $resource = new TreeSpeciesVersionResource($parentAndChild->parent, $parentAndChild->child);
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
