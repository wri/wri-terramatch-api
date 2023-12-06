<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Helpers\JsonResponseHelper;
use App\Jobs\NotifyVersionCreatedJob;
use App\Models\Pitch as PitchModel;
use App\Models\RestorationMethodMetric as RestorationMethodMetricModel;
use App\Models\RestorationMethodMetricVersion as RestorationMethodMetricVersionModel;
use App\Resources\RestorationMethodMetricResource;
use App\Resources\RestorationMethodMetricVersionResource;
use App\Services\Version\VersionService;
use App\Validators\RestorationMethodMetricValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestorationMethodMetricsController extends Controller
{
    protected $versionService = null;

    public function __construct(
        RestorationMethodMetricModel $restorationMethodMetricModel,
        RestorationMethodMetricVersionModel $restorationMethodMetricVersionModel
    ) {
        $this->versionService = new VersionService($restorationMethodMetricModel, $restorationMethodMetricVersionModel);
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\RestorationMethodMetric::class);
        $childData = $request->json()->all();
        RestorationMethodMetricValidator::validate('CREATE', $childData);
        $pitch = PitchModel::findOrFail($childData['pitch_id']);
        $this->authorize('update', $pitch);
        $parentData = [
            'pitch_id' => $childData['pitch_id'],
        ];
        unset($childData['pitch_id']);
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new RestorationMethodMetricVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('read', $parentAndChild->parent);
        $resource = new RestorationMethodMetricResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('update', $parentAndChild->parent);
        $childData = $request->json()->all();
        RestorationMethodMetricValidator::validate('UPDATE', $childData);
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new RestorationMethodMetricVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByPitchAction(PitchModel $pitch): JsonResponse
    {
        $this->authorize('read', $pitch);
        $parentsAndChildren = $this->versionService->findAllParents([['pitch_id', '=', $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new RestorationMethodMetricResource($parentAndChild->parent, $parentAndChild->child);
        }
        $resources = ArrayHelper::sortBy($resources, 'restoration_method', ArrayHelper::ASC);

        return JsonResponseHelper::success($resources, 200);
    }

    public function inspectByPitchAction(PitchModel $pitch): JsonResponse
    {
        $this->authorize('inspect', $pitch);
        $parentsAndChildren = $this->versionService->groupAllChildren([['pitch_id', '=', $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new RestorationMethodMetricVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        ArrayHelper::sortDataBy($resources, 'created_at', ArrayHelper::DESC);

        return JsonResponseHelper::success($resources, 200);
    }

    public function deleteAction(RestorationMethodMetricModel $restorationMethodMetric): JsonResponse
    {
        $this->authorize('delete', $restorationMethodMetric);
        $restorationMethodMetric->versions()->delete();
        $restorationMethodMetric->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
