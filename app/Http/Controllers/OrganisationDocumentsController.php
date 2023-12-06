<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreOrganisationDocumentsRequest;
use App\Http\Requests\UpdateOrganisationDocumentsRequest;
use App\Jobs\NotifyVersionCreatedJob;
use App\Models\Organisation as OrganisationModel;
use App\Models\OrganisationDocument as OrganisationDocumentModel;
use App\Models\OrganisationDocumentVersion as OrganisationDocumentVersionModel;
use App\Resources\OrganisationDocumentResource;
use App\Resources\OrganisationDocumentVersionResource;
use App\Services\Version\VersionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganisationDocumentsController extends Controller
{
    protected $versionService = null;

    public function __construct(
        OrganisationDocumentModel $organisationDocumentModel,
        OrganisationDocumentVersionModel $organisationDocumentVersionModel
    ) {
        $this->versionService = new VersionService($organisationDocumentModel, $organisationDocumentVersionModel);
    }

    public function createAction(StoreOrganisationDocumentsRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\OrganisationDocument::class);
        $childData = $request->json()->all();
        $me = Auth::user();
        $parentData = [
            'organisation_id' => $me->organisation_id,
        ];
        $childData['document'] = UploadHelper::findByIdAndValidate(
            $childData['document'],
            UploadHelper::IMAGES_FILES,
            $me->id
        );
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new OrganisationDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        if ($parentAndChild->child->type == 'legal') {
            throw new ModelNotFoundException();
        }
        $this->authorize('read', $parentAndChild->parent);
        $resource = new OrganisationDocumentResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function updateAction(UpdateOrganisationDocumentsRequest $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('update', $parentAndChild->parent);
        $childData = $request->json()->all();
        $me = Auth::user();
        if (array_key_exists('document', $childData)) {
            $childData['document'] = UploadHelper::findByIdAndValidate(
                $childData['document'],
                UploadHelper::IMAGES_FILES,
                $me->id
            );
        }
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new OrganisationDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByOrganisationAction(OrganisationModel $organisation): JsonResponse
    {
        $this->authorize('read', $organisation);
        $parentsAndChildren = $this->versionService->findAllParents([['organisation_id', '=', $organisation->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            if ($parentAndChild->child->type == 'legal') {
                continue;
            }
            $resources[] = new OrganisationDocumentResource($parentAndChild->parent, $parentAndChild->child);
        }
        $resources = ArrayHelper::sortBy($resources, 'name', ArrayHelper::ASC);

        return JsonResponseHelper::success($resources, 200);
    }

    public function inspectByOrganisationAction(OrganisationModel $organisation): JsonResponse
    {
        $this->authorize('inspect', $organisation);
        $parentsAndChildren = $this->versionService->groupAllChildren([['organisation_id', '=', $organisation->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new OrganisationDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        ArrayHelper::sortDataBy($resources, 'created_at', ArrayHelper::DESC);

        return JsonResponseHelper::success($resources, 200);
    }

    public function deleteAction(OrganisationDocumentModel $organisationDocument): JsonResponse
    {
        $this->authorize('delete', $organisationDocument);
        $organisationDocument->versions()->delete();
        $organisationDocument->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
