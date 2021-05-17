<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Jobs\NotifyVersionCreatedJob;
use App\Models\Organisation as OrganisationModel;
use App\Models\OrganisationVersion as OrganisationVersionModel;
use App\Resources\MaskedOrganisationResource;
use App\Resources\OrganisationResource;
use App\Resources\OrganisationVersionResource;
use App\Services\Version\VersionService;
use App\Validators\OrganisationValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganisationsController extends Controller
{
    protected $versionService = null;

    public function __construct(
        OrganisationModel $organisationModel,
        OrganisationVersionModel $organisationVersionModel
    ) {
        $this->versionService = new VersionService($organisationModel, $organisationVersionModel);
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\Organisation");
        $childData = $request->json()->all();
        OrganisationValidator::validate("CREATE", $childData);
        $me = Auth::user();
        $parentData = [];
        $childData["cover_photo"] = UploadHelper::findByIdAndValidate(
            $childData["cover_photo"], UploadHelper::IMAGES, $me->id
        );
        $childData["avatar"] = UploadHelper::findByIdAndValidate(
            $childData["avatar"], UploadHelper::IMAGES, $me->id
        );
        $childData["video"] = UploadHelper::findByIdAndValidate(
            $childData["video"], UploadHelper::VIDEOS, $me->id
        );
        UploadHelper::assertUnique($childData["cover_photo"], $childData["avatar"], $childData["video"]);
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $me->organisation_id = $parentAndChild->parent->id;
        $me->saveOrFail();
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);
        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("read", $parentAndChild->parent);
        $resource = new MaskedOrganisationResource($parentAndChild->parent, $parentAndChild->child);
        return JsonResponseHelper::success($resource, 200);
    }

    public function inspectAction(Request $request, int $id): JsonResponse
    {
        $parentsAndChildren = $this->versionService->groupAllChildren([["id", "=", $id]]);
        if (count($parentsAndChildren) < 1) {
            throw new ModelNotFoundException();
        }
        $parentAndChild = $parentsAndChildren[0];
        $this->authorize("inspect", $parentAndChild->parent);
        $resource = new OrganisationResource($parentAndChild->parent, $parentAndChild->child);
        return JsonResponseHelper::success($resource, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("update", $parentAndChild->parent);
        $childData = $request->json()->all();
        OrganisationValidator::validate("UPDATE", $childData);
        $me = Auth::user();
        if (array_key_exists("cover_photo", $childData)) {
            $childData["cover_photo"] = UploadHelper::findByIdAndValidate(
                $childData["cover_photo"], UploadHelper::IMAGES, $me->id
            );
        }
        if (array_key_exists("avatar", $childData)) {
            $childData["avatar"] = UploadHelper::findByIdAndValidate(
                $childData["avatar"], UploadHelper::IMAGES, $me->id
            );
        }
        if (array_key_exists("video", $childData)) {
            $childData["video"] = UploadHelper::findByIdAndValidate(
                $childData["video"], UploadHelper::VIDEOS, $me->id
            );
        }
        UploadHelper::assertUnique(@$childData["cover_photo"], @$childData["avatar"], @$childData["video"]);
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);
        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Organisation");
        $parentsAndChildren = $this->versionService->findAllParents();
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new OrganisationResource($parentAndChild->parent, $parentAndChild->child);
        }
        $resources = ArrayHelper::sortBy($resources, "name", ArrayHelper::ASC);
        return JsonResponseHelper::success($resources, 200);
    }
}
