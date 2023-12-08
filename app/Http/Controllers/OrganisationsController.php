<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreOrganisationRequest;
use App\Http\Requests\UpdateOrganisationRequest;
use App\Jobs\NotifyVersionCreatedJob;
use App\Models\Organisation as OrganisationModel;
use App\Models\OrganisationVersion as OrganisationVersionModel;
use App\Resources\MaskedOrganisationResource;
use App\Resources\OrganisationLiteResource;
use App\Resources\OrganisationResource;
use App\Resources\OrganisationVersionResource;
use App\Services\Version\VersionService;
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

    public function createAction(StoreOrganisationRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Organisation::class);
        $childData = $request->json()->all();
        $me = Auth::user();
        $parentData = [];
        if (! is_null($childData['cover_photo'])) {
            $childData['cover_photo'] = UploadHelper::findByIdAndValidate(
                $childData['cover_photo'],
                UploadHelper::IMAGES,
                $me->id
            );
        }
        if (! is_null($childData['avatar'])) {
            $childData['avatar'] = UploadHelper::findByIdAndValidate(
                $childData['avatar'],
                UploadHelper::IMAGES,
                $me->id
            );
        }
        if (! is_null($childData['video'])) {
            $childData['video'] = UploadHelper::findByIdAndValidate(
                $childData['video'],
                UploadHelper::VIDEOS,
                $me->id
            );
        }

        UploadHelper::assertUnique(
            $childData['cover_photo'],
            $childData['avatar'],
            $childData['video'],
        );
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);

        $me->update(['organisation_id' => $parentAndChild->parent->id]);

        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('read', $parentAndChild->parent);
        $resource = new MaskedOrganisationResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function inspectAction(Request $request, int $id): JsonResponse
    {
        $parentsAndChildren = $this->versionService->groupAllChildren([['id', '=', $id]]);
        if (count($parentsAndChildren) < 1) {
            throw new ModelNotFoundException();
        }
        $parentAndChild = $parentsAndChildren[0];
        $this->authorize('inspect', $parentAndChild->parent);
        $resource = new OrganisationResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function updateAction(UpdateOrganisationRequest $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('update', $parentAndChild->parent);
        $childData = $request->json()->all();
        $me = Auth::user();
        if (array_key_exists('cover_photo', $childData)) {
            $childData['cover_photo'] = UploadHelper::findByIdAndValidate(
                $childData['cover_photo'],
                UploadHelper::IMAGES,
                $me->id
            );
        }
        if (array_key_exists('avatar', $childData)) {
            $childData['avatar'] = UploadHelper::findByIdAndValidate(
                $childData['avatar'],
                UploadHelper::IMAGES,
                $me->id
            );
        }
        if (array_key_exists('video', $childData)) {
            $childData['video'] = UploadHelper::findByIdAndValidate(
                $childData['video'],
                UploadHelper::VIDEOS,
                $me->id
            );
        }
        UploadHelper::assertUnique(@$childData['cover_photo'], @$childData['avatar'], @$childData['video']);
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', 'App\\Models\\Organisation');

        $organisationVersions = OrganisationVersionModel::with('organisation')
            ->where('status', 'approved')
            ->orderBy('name', 'asc')
            ->get();

        $resources = [];
        foreach ($organisationVersions as $version) {
            $resources[] = new OrganisationLiteResource($version->organisation, $version);
        }

        return JsonResponseHelper::success($resources, 200);
    }
}
