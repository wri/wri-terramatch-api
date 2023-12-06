<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreTreeSpeciesRequest;
use App\Http\Requests\UpdateTreeSpeciesRequest;
use App\Jobs\NotifyVersionCreatedJob;
use App\Models\Pitch as PitchModel;
use App\Models\TreeSpecies as TreeSpeciesModel;
use App\Models\TreeSpeciesVersion as TreeSpeciesVersionModel;
use App\Resources\TreeSpeciesResource;
use App\Resources\TreeSpeciesVersionResource;
use App\Services\Version\VersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TreeSpeciesController extends Controller
{
    protected $versionService = null;

    public function __construct(TreeSpeciesModel $treeSpeciesModel, TreeSpeciesVersionModel $treeSpeciesVersionModel)
    {
        $this->versionService = new VersionService($treeSpeciesModel, $treeSpeciesVersionModel);
    }

    public function createAction(StoreTreeSpeciesRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\TreeSpecies::class);
        $childData = $request->all();
        $pitch = PitchModel::findOrFail($childData['pitch_id']);
        $this->authorize('update', $pitch);
        $parentData = [
            'pitch_id' => $childData['pitch_id'],
        ];
        unset($childData['pitch_id']);
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new TreeSpeciesVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('read', $parentAndChild->parent);
        $resource = new TreeSpeciesResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function updateAction(UpdateTreeSpeciesRequest $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('update', $parentAndChild->parent);
        $childData = $request->all();
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new TreeSpeciesVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = PitchModel::findOrFail($id);
        $this->authorize('read', $pitch);
        $parentsAndChildren = $this->versionService->findAllParents([['pitch_id', '=', $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new TreeSpeciesResource($parentAndChild->parent, $parentAndChild->child);
        }
        $resources = ArrayHelper::sortBy($resources, 'name', ArrayHelper::ASC);

        return JsonResponseHelper::success($resources, 200);
    }

    public function inspectByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = PitchModel::findOrFail($id);
        $this->authorize('inspect', $pitch);
        $parentsAndChildren = $this->versionService->groupAllChildren([['pitch_id', '=', $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new TreeSpeciesVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        ArrayHelper::sortDataBy($resources, 'created_at', ArrayHelper::DESC);

        return JsonResponseHelper::success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $treeSpecies = TreeSpeciesModel::findOrFail($id);
        $this->authorize('delete', $treeSpecies);
        $treeSpecies->versions()->delete();
        $treeSpecies->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
