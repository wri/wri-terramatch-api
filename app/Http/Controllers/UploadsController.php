<?php

namespace App\Http\Controllers;

use App\Resources\UploadResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use App\Validators\UploadValidator;
use App\Services\FileService;
use Exception;
use App\Models\Upload as UploadModel;
use Illuminate\Auth\AuthManager;

class UploadsController extends Controller
{
    private $jsonResponseFactory = null;
    private $uploadValidator = null;
    private $fileService = null;
    private $uploadModel = null;
    private $authManager = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        UploadValidator $uploadValidator,
        FileService $fileService,
        UploadModel $uploadModel,
        AuthManager $authManager
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->uploadValidator = $uploadValidator;
        $this->fileService = $fileService;
        $this->uploadModel = $uploadModel;
        $this->authManager = $authManager;
    }

    /**
     * This method is the only one that uses traditional POST data in the
     * request instead of JSON. If we were to accept JSON we would have to take
     * strings of base 64 encoded files, which increases their size by 33.3%...
     * Some older devices also won't be able to handle applying a base 64
     * function to an entire video in browser.
     */
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\Upload");
        $data = $request->all();
        $this->uploadValidator->validate("create", $data);
        $file = $request->files->get("upload");
        switch ($file->getMimeType()) {
            case "image/jpeg":
            case "image/png":
            case "image/gif":
                $this->uploadValidator->validate("createImage", $data);
                break;
            case "video/mp4":
                $this->uploadValidator->validate("createVideo", $data);
                break;
            case "application/pdf":
                $this->uploadValidator->validate("createDocument", $data);
                break;
            default:
                throw new Exception();
        }
        $me = $this->authManager->user();
        $upload = $this->uploadModel->newInstance();
        $upload->user_id = $me->id;
        $upload->location = $this->fileService->create($file->getPathname(), $file->getMimeType());
        $upload->saveOrFail();
        return $this->jsonResponseFactory->success(new UploadResource($upload), 201);
    }
}
