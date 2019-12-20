<?php

namespace App\Http\Controllers;

use App\Http\JsonResponseFactory;
use App\Models\RestorationMethodMetric;
use App\Models\RestorationMethodMetricVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Validators\RestorationMethodMetricValidator;
use App\Models\RestorationMethodMetric as RestorationMethodMetricModel;
use App\Models\RestorationMethodMetricVersion as RestorationMethodMetricVersionModel;
use App\Models\Pitch as PitchModel;
use App\Services\VersionService;
use App\Resources\RestorationMethodMetricResource;
use App\Resources\RestorationMethodMetricVersionResource;
use App\Services\NotificationService;

class RestorationMethodMetricsController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $restorationMethodMetricValidator = null;
    protected $versionService = null;
    protected $pitchModel = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        RestorationMethodMetricValidator $restorationMethodMetricValidator,
        RestorationMethodMetricModel $restorationMethodMetricModel,
        RestorationMethodMetricVersionModel $restorationMethodMetricVersionModel,
        PitchModel $pitchModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->restorationMethodMetricValidator = $restorationMethodMetricValidator;
        $this->versionService = new VersionService($restorationMethodMetricModel, $restorationMethodMetricVersionModel);
        $this->pitchModel = $pitchModel;
        $this->notificationService = $notificationService;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\RestorationMethodMetric");
        $childData = $request->json()->all();
        $this->restorationMethodMetricValidator->validate("create", $childData);
        $pitch = $this->pitchModel->findOrFail($childData["pitch_id"]);
        $this->authorize("update", $pitch);
        $parentData = [
            "pitch_id" => $childData["pitch_id"]
        ];
        unset($childData["pitch_id"]);
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new RestorationMethodMetricVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("read", $parentAndChild->parent);
        $resource = new RestorationMethodMetricResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("update", $parentAndChild->parent);
        $childData = $request->json()->all();
        $this->restorationMethodMetricValidator->validate("update", $childData);
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new RestorationMethodMetricVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = $this->pitchModel->findOrFail($id);
        $this->authorize("read", $pitch);
        $parentsAndChildren = $this->versionService->findAllParents([["pitch_id", "=", $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new RestorationMethodMetricResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function inspectByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = $this->pitchModel->findOrFail($id);
        $this->authorize("inspect", $pitch);
        $parentsAndChildren = $this->versionService->groupAllChildren([["pitch_id", "=", $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new RestorationMethodMetricVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $restorationMethodMetric = RestorationMethodMetric::findOrFail($id);
        $this->authorize("delete", $restorationMethodMetric);
        $restorationMethodMetric->versions()->delete();
        $restorationMethodMetric->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}
