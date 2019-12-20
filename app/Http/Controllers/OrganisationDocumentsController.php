<?php

namespace App\Http\Controllers;

use App\Http\JsonResponseFactory;
use App\Models\OrganisationDocumentVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Validators\OrganisationDocumentValidator;
use Illuminate\Auth\AuthManager;
use App\Models\OrganisationDocument as OrganisationDocumentModel;
use App\Models\OrganisationDocumentVersion as OrganisationDocumentVersionModel;
use App\Models\Organisation as OrganisationModel;
use App\Services\FileService;
use App\Models\Upload as UploadModel;
use App\Services\VersionService;
use App\Resources\OrganisationDocumentResource;
use App\Resources\OrganisationDocumentVersionResource;
use App\Services\NotificationService;

class OrganisationDocumentsController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $organisationDocumentValidator = null;
    protected $authManager = null;
    protected $fileService = null;
    protected $uploadModel = null;
    protected $versionService = null;
    protected $organisationModel = null;
    protected $notificationService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        OrganisationDocumentValidator $organisationDocumentValidator,
        AuthManager $authManager,
        FileService $fileService,
        UploadModel $uploadModel,
        OrganisationDocumentModel $organisationDocumentModel,
        OrganisationDocumentVersionModel $organisationDocumentVersionModel,
        OrganisationModel $organisationModel,
        NotificationService $notificationService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->organisationDocumentValidator = $organisationDocumentValidator;
        $this->authManager = $authManager;
        $this->fileService = $fileService;
        $this->uploadModel = $uploadModel;
        $this->versionService = new VersionService($organisationDocumentModel, $organisationDocumentVersionModel);
        $this->organisationModel = $organisationModel;
        $this->notificationService = $notificationService;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\OrganisationDocument");
        $childData = $request->json()->all();
        $this->organisationDocumentValidator->validate("create", $childData);
        $me = $this->authManager->user();
        $parentData = [
            "organisation_id" => $me->organisation_id
        ];
        $childData["document"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
            $childData["document"], UploadModel::IMAGES_FILES, $me->id
        );
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new OrganisationDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("read", $parentAndChild->parent);
        $resource = new OrganisationDocumentResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize("update", $parentAndChild->parent);
        $childData = $request->json()->all();
        $this->organisationDocumentValidator->validate("update", $childData);
        $me = $this->authManager->user();
        if (array_key_exists("document", $childData)) {
            $childData["document"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $childData["document"], UploadModel::IMAGES_FILES, $me->id
            );
        }
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        $this->notificationService->notifyVersionCreated($parentAndChild->child);
        $resource = new OrganisationDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        return $this->jsonResponseFactory->success($resource, 200);
    }

    public function readAllByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = $this->organisationModel->findOrFail($id);
        $this->authorize("read", $organisation);
        $parentsAndChildren = $this->versionService->findAllParents([["organisation_id", "=", $organisation->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new OrganisationDocumentResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function inspectByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = $this->organisationModel->findOrFail($id);
        $this->authorize("inspect", $organisation);
        $parentsAndChildren = $this->versionService->groupAllChildren([["organisation_id", "=", $organisation->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new OrganisationDocumentVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $organisationDocument = OrganisationDocumentModel::findOrFail($id);
        $this->authorize("delete", $organisationDocument);
        $organisationDocument->versions()->delete();
        $organisationDocument->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}
