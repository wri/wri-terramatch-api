<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreOrganisationFileRequest;
use App\Models\Organisation;
use App\Models\OrganisationFile;
use App\Models\OrganisationVersion;
use App\Resources\OrganisationFileResource;
use App\Services\Version\VersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganisationFileController extends Controller
{
    protected $versionService = null;

    public function __construct(
        Organisation $organisationModel,
        OrganisationVersion $organisationVersionModel
    ) {
        $this->versionService = new VersionService($organisationModel, $organisationVersionModel);
    }

    public function createAction(StoreOrganisationFileRequest $request): JsonResponse
    {
        $data = $request->json()->all();
        $parentAndChild = $this->versionService->findParent($data['organisation_id']);
        $this->authorize('update', $parentAndChild->parent);

        $me = Auth::user();

        $data['upload'] = UploadHelper::findByIdAndValidate(
            $data['upload'],
            UploadHelper::FILES_PDF,
            $me->id
        );

        $organisationFile = OrganisationFile::create($data);

        return JsonResponseHelper::success(new OrganisationFileResource($organisationFile), 201);
    }

    public function deleteAction(Request $request, OrganisationFile $organisationFile): JsonResponse
    {
        $this->authorize('update', $organisationFile->organisation);

        $organisationFile->delete();

        return JsonResponseHelper::success([], 200);
    }

    public function readByOrganisationAction(Request $request, Organisation $organisation): JsonResponse
    {
        $resources = [];

        $parentAndChild = $this->versionService->findParent($organisation->id);
        $this->authorize('inspect', $parentAndChild->parent);

        foreach ($parentAndChild->parent->organisationFiles as $file) {
            $resources[] = new OrganisationFileResource($file);
        }

        return JsonResponseHelper::success($resources, 200);
    }
}
