<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use App\Validators\OfferDocumentValidator;
use App\Models\Offer as OfferModel;
use App\Models\OfferDocument as OfferDocumentModel;
use App\Models\Upload as UploadModel;
use App\Resources\OfferDocumentResource;
use Illuminate\Auth\AuthManager;
use App\Services\FileService;

class OfferDocumentsController extends Controller
{
    private $jsonResponseFactory = null;
    private $offerDocumentValidator = null;
    private $offerModel = null;
    private $offerDocumentModel = null;
    private $uploadModel = null;
    protected $authManager = null;
    protected $fileService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        OfferDocumentValidator $offerDocumentValidator,
        AuthManager $authManager,
        OfferModel $offerModel,
        OfferDocumentModel $offerDocumentModel,
        UploadModel $uploadModel,
        FileService $fileService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->offerDocumentValidator = $offerDocumentValidator;
        $this->offerModel = $offerModel;
        $this->offerDocumentModel = $offerDocumentModel;
        $this->uploadModel = $uploadModel;
        $this->authManager = $authManager;
        $this->fileService = $fileService;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\OfferDocument");
        $data = $request->json()->all();
        $this->offerDocumentValidator->validate("create", $data);
        $offer = $this->offerModel->findOrFail($data["offer_id"]);
        $this->authorize("update", $offer);
        $me = $this->authManager->user();
        $data["document"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
            $data["document"], UploadModel::IMAGES_FILES, $me->id
        );
        $offerDocument = $this->offerDocumentModel->newInstance($data);
        $offerDocument->saveOrFail();
        $offerDocument->refresh();
        return $this->jsonResponseFactory->success(new OfferDocumentResource($offerDocument), 201);
    }

    public function readAllByOfferAction(Request $request, int $id): JsonResponse
    {
        $offer = $this->offerModel->findOrFail($id);
        $this->authorize("read", $offer);
        $offerDocuments = $this->offerDocumentModel->where("offer_id", "=", $offer->id)->get();
        $resources = [];
        foreach ($offerDocuments as $offerDocument) {
            $resources[] = new OfferDocumentResource($offerDocument);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $offerDocument = $this->offerDocumentModel->findOrFail($id);
        $this->authorize("read", $offerDocument);
        return $this->jsonResponseFactory->success(new OfferDocumentResource($offerDocument), 200);
    }

    public function updateAction(Request $request, $id): JsonResponse
    {
        $offerDocument = $this->offerDocumentModel->findOrFail($id);
        $this->authorize("update", $offerDocument);
        $data = $request->json()->all();
        $this->offerDocumentValidator->validate("update", $data);
        $me = $this->authManager->user();
        if (array_key_exists("document", $data)) {
            $data["document"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $data["document"], UploadModel::IMAGES_FILES, $me->id
            );
        }
        $offerDocument->fill($data);
        $offerDocument->saveOrFail();
        return $this->jsonResponseFactory->success(new OfferDocumentResource($offerDocument), 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $offerDocument = $this->offerDocumentModel->findOrFail($id);
        $this->authorize("delete", $offerDocument);
        $this->fileService->delete($offerDocument->document);
        $offerDocument->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}
