<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreDocumentFileRequest;
use App\Http\Requests\UpdateDocumentFileRequest;
use App\Models\DocumentFile;
use App\Resources\DocumentFileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentFileController extends Controller
{
    public function createAction(StoreDocumentFileRequest $request): JsonResponse
    {
        $data = $request->json()->all();

        $documentFileable = getDataFromMorphable($data['document_fileable_type'], $data['document_fileable_id']);

        $this->authorize('createFile', $documentFileable['model']);

        $me = Auth::user();
        $data['upload'] = UploadHelper::findByIdAndValidate(
            $data['upload'],
            $documentFileable['files'],
            $me->id
        );

        $extra = [
            'document_fileable_type' => get_class($documentFileable['model']),
        ];

        $documentFile = DocumentFile::create(array_merge($data, $extra));

        return JsonResponseHelper::success(new DocumentFileResource($documentFile), 201);
    }

    public function readAction(Request $request, $uuid): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $documentFile = DocumentFile::where('uuid', $uuid)->firstOrFail();

        return JsonResponseHelper::success(new DocumentFileResource($documentFile));
    }

    public function updateAction(UpdateDocumentFileRequest $request, $uuid): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $data = $request->all();
        $documentFile = DocumentFile::where('uuid', $uuid)->firstOrFail();

        $documentFile->collection = $data['collection'] ?? $documentFile->collection;
        $documentFile->title = $data['title'] ?? $documentFile->title;
        $documentFile->is_public = $data['is_public'] ?? $documentFile->is_public;

        $documentFile->saveOrFail();

        return JsonResponseHelper::success(new DocumentFileResource($documentFile), 200);
    }

    public function downloadExample(Request $request, string $templateName = null): BinaryFileResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        if (empty($templateName)) {
            return JsonResponseHelper::error(['No template specified'], 404);
        }

        $headers = ['Content-Type' => 'text/plain'];

        switch ($templateName) {
            case 'report_species_planted':
                $filename = 'report_species_planted.csv';
                $path = base_path('resources/templates/report_species_planted.csv');

                break;
            default:
                return JsonResponseHelper::error(['Specified template not found.'], 404);
        }

        return response()->download($path, $filename, $headers)->deleteFileAfterSend(true);
    }

    public function deleteAction(string $uuid): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        if (empty($uuid)) {
            return JsonResponseHelper::error(['No file specified'], 404);
        }

        DocumentFile::where('uuid', '=', $uuid)->delete();

        return JsonResponseHelper::success(['delete request has been processed'], 200);
    }
}
