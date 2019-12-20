<?php

namespace App\Http\Controllers;

use App\Http\JsonResponseFactory;
use App\Models\PitchDocumentVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Validators\PitchDocumentValidator;
use Illuminate\Auth\AuthManager;
use App\Models\PitchDocument as PitchDocumentModel;
use App\Models\PitchDocumentVersion as PitchDocumentVersionModel;
use App\Models\Pitch as PitchModel;
use App\Models\Upload as UploadModel;
use App\Services\VersionService;
use App\Resources\PitchDocumentResource;
use App\Resources\PitchDocumentVersionResource;
use App\Services\NotificationService;

class PitchDocumentsController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $pitchDocumentValidator = null;
    protected $authManager = null;
    protected $fileService = null;
    protected $uploadModel = null;
    protected $versionService = null;
    protected $pitchModel = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        PitchDocumentValidator $pitchDocumentValidator,
        AuthManager $authManager,
        UploadModel $uploadModel,
        PitchDocumentModel $pitchDocumentModel,
        PitchDocumentVersionModel $pitchDocumentVersionModel,
        PitchModel $pitchModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->pitchDocumentValidator = $pitchDocumentValidator;
        $this->authManager = $authManager;
        $this->uploadModel = $uploadModel;
        $this->versionService = new VersionService($pitchDocumentModel, $pitchDocumentVersionModel);
        $this->pitchModel = $pitchModel;
        $this->notificationService = $notificationService;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\PitchDocument");
        $childData = $request->json()->all();
        $this->pitchDocumentValidator->validate("create", $childData);
        $pitch = $this->pitchModel->findOrFail($childData["pitch_id"]);
        $this->authorize("update", $pitch);
        $parentData = [
            "pitch_id" => $childData["pitch_id"]
        ];
        unset($childData["pitch_id"]);
        $me = $this->authManager->user();
        $childData["document"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
            $childData["document"], UploadModel::IMAGES_FILES, $me->id
        );
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new PitchDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("read", $parentAndChild->parent);
        $resource = new PitchDocumentResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("update", $parentAndChild->parent);
        $childData = $request->json()->all();
        $this->pitchDocumentValidator->validate("update", $childData);
        $me = $this->authManager->user();
        if (array_key_exists("document", $childData)) {
            $childData["document"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $childData["document"], UploadModel::IMAGES_FILES, $me->id
            );
        }
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new PitchDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = $this->pitchModel->findOrFail($id);
        $this->authorize("read", $pitch);
        $parentsAndChildren = $this->versionService->findAllParents([["pitch_id", "=", $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new PitchDocumentResource($parentAndChild->parent, $parentAndChild->child);
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
            $resources[] = new PitchDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $pitchDocument = PitchDocumentModel::findOrFail($id);
        $this->authorize("delete", $pitchDocument);
        $pitchDocument->versions()->delete();
        $pitchDocument->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}
