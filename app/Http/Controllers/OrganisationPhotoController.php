<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreOrganisationPhotoRequest;
use App\Models\Organisation;
use App\Models\OrganisationPhoto;
use App\Models\OrganisationVersion;
use App\Resources\OrganisationPhotoResource;
use App\Services\Version\VersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganisationPhotoController extends Controller
{
    protected $versionService = null;

    public function __construct(
        Organisation $organisationModel,
        OrganisationVersion $organisationVersionModel
    ) {
        $this->versionService = new VersionService($organisationModel, $organisationVersionModel);
    }

    public function createAction(StoreOrganisationPhotoRequest $request): JsonResponse
    {
        $data = $request->json()->all();
        $parentAndChild = $this->versionService->findParent($data['organisation_id']);
        $this->authorize('update', $parentAndChild->parent);

        $me = Auth::user();

        $data['upload'] = UploadHelper::findByIdAndValidate(
            $data['upload'],
            UploadHelper::IMAGES,
            $me->id
        );

        $organisationPhoto = OrganisationPhoto::create($data);

        return JsonResponseHelper::success(new OrganisationPhotoResource($organisationPhoto), 201);
    }

    public function deleteAction(Request $request, OrganisationPhoto $organisationPhoto): JsonResponse
    {
        $this->authorize('update', $organisationPhoto->organisation);

        $organisationPhoto->delete();

        return JsonResponseHelper::success([], 200);
    }
}
