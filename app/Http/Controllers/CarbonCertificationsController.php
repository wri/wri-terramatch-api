<?php

namespace App\Http\Controllers;

use App\Http\JsonResponseFactory;
use App\Models\CarbonCertification;
use App\Models\CarbonCertificationVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Validators\CarbonCertificationValidator;
use App\Models\CarbonCertification as CarbonCertificationModel;
use App\Models\CarbonCertificationVersion as CarbonCertificationVersionModel;
use App\Models\Pitch as PitchModel;
use App\Services\VersionService;
use App\Resources\CarbonCertificationResource;
use App\Resources\CarbonCertificationVersionResource;
use App\Services\NotificationService;

class CarbonCertificationsController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $carbonCertificationValidator = null;
    protected $versionService = null;
    protected $pitchModel = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        CarbonCertificationValidator $carbonCertificationValidator,
        CarbonCertificationModel $carbonCertificationModel,
        CarbonCertificationVersionModel $carbonCertificationVersionModel,
        PitchModel $pitchModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->carbonCertificationValidator = $carbonCertificationValidator;
        $this->versionService = new VersionService($carbonCertificationModel, $carbonCertificationVersionModel);
        $this->pitchModel = $pitchModel;
        $this->notificationService = $notificationService;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\CarbonCertification");
        $childData = $request->json()->all();
        $this->carbonCertificationValidator->validate("create", $childData);
        $pitch = $this->pitchModel->findOrFail($childData["pitch_id"]);
        $this->authorize("update", $pitch);
        $parentData = [
            "pitch_id" => $childData["pitch_id"]
        ];
        unset($childData["pitch_id"]);
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new CarbonCertificationVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("read", $parentAndChild->parent);
        $resource = new CarbonCertificationResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("update", $parentAndChild->parent);
        $childData = $request->json()->all();
        $this->carbonCertificationValidator->validate("update", $childData);
        // When type is not other, other_type property must be null.
        if($childData['type'] !== 'other')
        {
            $childData['other_type'] = null;
        }
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new CarbonCertificationVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = $this->pitchModel->findOrFail($id);
        $this->authorize("read", $pitch);
        $parentsAndChildren = $this->versionService->findAllParents([["pitch_id", "=", $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new CarbonCertificationResource($parentAndChild->parent, $parentAndChild->child);
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
            $resources[] = new CarbonCertificationVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $carbonCertification = CarbonCertification::findOrFail($id);
        $this->authorize("delete", $carbonCertification);
        $carbonCertification->versions()->delete();
        $carbonCertification->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}
