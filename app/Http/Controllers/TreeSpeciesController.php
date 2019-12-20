<?php

namespace App\Http\Controllers;

use App\Http\JsonResponseFactory;
use App\Models\TreeSpecies;
use App\Models\TreeSpeciesVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Validators\TreeSpeciesValidator;
use App\Models\TreeSpecies as TreeSpeciesModel;
use App\Models\TreeSpeciesVersion as TreeSpeciesVersionModel;
use App\Models\Pitch as PitchModel;
use App\Services\VersionService;
use App\Resources\TreeSpeciesResource;
use App\Resources\TreeSpeciesVersionResource;
use App\Services\NotificationService;

class TreeSpeciesController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $treeSpeciesValidator = null;
    protected $versionService = null;
    protected $pitchModel = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        TreeSpeciesValidator $treeSpeciesValidator,
        TreeSpeciesModel $treeSpeciesModel,
        TreeSpeciesVersionModel $treeSpeciesVersionModel,
        PitchModel $pitchModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->treeSpeciesValidator = $treeSpeciesValidator;
        $this->versionService = new VersionService($treeSpeciesModel, $treeSpeciesVersionModel);
        $this->pitchModel = $pitchModel;
        $this->notificationService = $notificationService;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\TreeSpecies");
        $childData = $request->json()->all();
        $this->treeSpeciesValidator->validate("create", $childData);
        $pitch = $this->pitchModel->findOrFail($childData["pitch_id"]);
        $this->authorize("update", $pitch);
        $parentData = [
            "pitch_id" => $childData["pitch_id"]
        ];
        unset($childData["pitch_id"]);
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new TreeSpeciesVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("read", $parentAndChild->parent);
        $resource = new TreeSpeciesResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("update", $parentAndChild->parent);
        $childData = $request->json()->all();
        $this->treeSpeciesValidator->validate("update", $childData);
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new TreeSpeciesVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = $this->pitchModel->findOrFail($id);
        $this->authorize("read", $pitch);
        $parentsAndChildren = $this->versionService->findAllParents([["pitch_id", "=", $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new TreeSpeciesResource($parentAndChild->parent, $parentAndChild->child);
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
            $resources[] = new TreeSpeciesVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $treeSpecies = TreeSpecies::findOrFail($id);
        $this->authorize("delete", $treeSpecies);
        $treeSpecies->versions()->delete();
        $treeSpecies->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}
