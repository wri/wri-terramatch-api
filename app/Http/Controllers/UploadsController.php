<?php

namespace App\Http\Controllers;

use App\Exceptions\CorruptedUploadException;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Models\Upload as UploadModel;
use App\Resources\UploadResource;
use App\Validators\UploadValidator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UploadsController extends Controller
{
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
        UploadValidator::validate("CREATE", $data);
        $file = $request->files->get("upload");
        if ($file->getClientMimeType() !== $file->getMimeType()) {
            throw new CorruptedUploadException();
        }
        $maxSize = UploadHelper::MAX_FILESIZES[$file->getMimeType()] ?? 0;
        Validator::make($data, ["upload" => "between:0," . $maxSize,])->validate();
        $me = Auth::user();
        $fileService = App::make("App\\Services\\FileService");
        $upload = new UploadModel();
        $upload->user_id = $me->id;
        $upload->location = $fileService->create($file->getPathname(), $file->getMimeType());
        $upload->saveOrFail();
        return JsonResponseHelper::success(new UploadResource($upload), 201);
    }
}
