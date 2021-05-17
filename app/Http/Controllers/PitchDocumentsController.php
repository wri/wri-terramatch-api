<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Jobs\NotifyVersionCreatedJob;
use App\Models\Pitch as PitchModel;
use App\Models\PitchDocument as PitchDocumentModel;
use App\Models\PitchDocumentVersion as PitchDocumentVersionModel;
use App\Resources\PitchDocumentResource;
use App\Resources\PitchDocumentVersionResource;
use App\Services\Version\VersionService;
use App\Validators\PitchDocumentValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PitchDocumentsController extends Controller
{
    protected $versionService = null;

    public function __construct(
        PitchDocumentModel $pitchDocumentModel,
        PitchDocumentVersionModel $pitchDocumentVersionModel
    ) {
        $this->versionService = new VersionService($pitchDocumentModel, $pitchDocumentVersionModel);
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\PitchDocument");
        $childData = $request->json()->all();
        PitchDocumentValidator::validate("CREATE", $childData);
        $pitch = PitchModel::findOrFail($childData["pitch_id"]);
        $this->authorize("update", $pitch);
        $parentData = [
            "pitch_id" => $childData["pitch_id"]
        ];
        unset($childData["pitch_id"]);
        $me = Auth::user();
        $childData["document"] = UploadHelper::findByIdAndValidate(
            $childData["document"], UploadHelper::IMAGES_FILES, $me->id
        );
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new PitchDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("read", $parentAndChild->parent);
        $resource = new PitchDocumentResource($parentAndChild->parent, $parentAndChild->child);
        return JsonResponseHelper::success($resource, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("update", $parentAndChild->parent);
        $childData = $request->json()->all();
        PitchDocumentValidator::validate("UPDATE", $childData);
        $me = Auth::user();
        if (array_key_exists("document", $childData)) {
            $childData["document"] = UploadHelper::findByIdAndValidate(
                $childData["document"], UploadHelper::IMAGES_FILES, $me->id
            );
        }
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new PitchDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = PitchModel::findOrFail($id);
        $this->authorize("read", $pitch);
        $parentsAndChildren = $this->versionService->findAllParents([["pitch_id", "=", $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new PitchDocumentResource($parentAndChild->parent, $parentAndChild->child);
        }
        $resources = ArrayHelper::sortBy($resources, "name", ArrayHelper::ASC);
        return JsonResponseHelper::success($resources, 200);
    }

    public function inspectByPitchAction(Request $request, int $id): JsonResponse
    {
        $pitch = PitchModel::findOrFail($id);
        $this->authorize("inspect", $pitch);
        $parentsAndChildren = $this->versionService->groupAllChildren([["pitch_id", "=", $pitch->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new PitchDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        ArrayHelper::sortDataBy($resources, "created_at", ArrayHelper::DESC);
        return JsonResponseHelper::success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $pitchDocument = PitchDocumentModel::findOrFail($id);
        $this->authorize("delete", $pitchDocument);
        $pitchDocument->versions()->delete();
        $pitchDocument->delete();
        return JsonResponseHelper::success((object) [], 200);
    }
}
