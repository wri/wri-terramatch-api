<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreCarbonCertificationsRequest;
use App\Http\Requests\UpdateCarbonCertificationsRequest;
use App\Jobs\NotifyVersionCreatedJob;
use App\Models\CarbonCertification as CarbonCertificationModel;
use App\Models\CarbonCertificationVersion as CarbonCertificationVersionModel;
use App\Models\Pitch as PitchModel;
use App\Resources\CarbonCertificationResource;
use App\Resources\CarbonCertificationVersionResource;
use App\Services\Version\VersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarbonCertificationsController extends Controller
{
    protected $versionService = null;

    protected $pitchModel = null;

    public function __construct(
        CarbonCertificationModel $carbonCertificationModel,
        CarbonCertificationVersionModel $carbonCertificationVersionModel
    ) {
        $this->versionService = new VersionService($carbonCertificationModel, $carbonCertificationVersionModel);
    }

    public function createAction(StoreCarbonCertificationsRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\CarbonCertification::class);
        $childData = $request->json()->all();
        $pitch = PitchModel::findOrFail($childData['pitch_id']);
        $this->authorize('update', $pitch);
        $parentData = [
            'pitch_id' => $childData['pitch_id'],
        ];
        unset($childData['pitch_id']);
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new CarbonCertificationVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('read', $parentAndChild->parent);
        $resource = new CarbonCertificationResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function updateAction(UpdateCarbonCertificationsRequest $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('update', $parentAndChild->parent);
        $childData = $request->json()->all();
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new CarbonCertificationVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = PitchModel::findOrFail($id);
        $this->authorize('read', $pitch);
        $parentsAndChildren = $this->versionService->findAllParents([['pitch_id', '=', $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new CarbonCertificationResource($parentAndChild->parent, $parentAndChild->child);
        }
        $resources = ArrayHelper::sortBy($resources, 'type', ArrayHelper::ASC);

        return JsonResponseHelper::success($resources, 200);
    }

    public function inspectByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = PitchModel::findOrFail($id);
        $this->authorize('inspect', $pitch);
        $parentsAndChildren = $this->versionService->groupAllChildren([['pitch_id', '=', $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new CarbonCertificationVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        ArrayHelper::sortDataBy($resources, 'created_at', ArrayHelper::DESC);

        return JsonResponseHelper::success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $carbonCertification = CarbonCertificationModel::findOrFail($id);
        $this->authorize('delete', $carbonCertification);
        $carbonCertification->versions()->delete();
        $carbonCertification->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
