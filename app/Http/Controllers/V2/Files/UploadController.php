<?php

namespace App\Http\Controllers\V2\Files;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\File\UploadRequest;
use App\Http\Resources\V2\Files\FileResource;
use App\Models\V2\MediaModel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use mysql_xdevapi\Exception;

class UploadController extends Controller
{
    public function __invoke(UploadRequest $request, string $collection, MediaModel $mediaModel)
    {
        $this->authorize('uploadFiles', $mediaModel);
        $config = $this->getConfiguration($mediaModel, $collection);
        $this->validateFile($request, $config);

        $qry = $mediaModel->addMediaFromRequest('upload_file');
        $this->prepHandler($qry, $request->all(), $mediaModel, $config, $collection);
        $details = $this->executeHandler($qry, $collection);

        if (Arr::has($request->all(), ['lat', 'lng'])) {
            $this->saveFileCoordinates($details, $request);
        }

        $this->saveAdditionalFileProperties($details, $request, $config);

        return new FileResource($details);
    }

    private function getConfiguration(MediaModel $mediaModel, $collection): array
    {
        $config = $mediaModel->fileConfiguration[$collection];

        if (empty($config)) {
            throw new Exception('Collection is unknown to this model.');
        }

        return $config;
    }

    private function validateFile($request, $config): void
    {
        $rules = config('wri.file-handling.validation-rules.' . data_get($config, 'validation', ''), '');

        if (empty($rules)) {
            throw new Exception('validation has not been set up for this collection.');
        }

        $validator = Validator::make($request->all(), [
            'upload_file' => $rules,
        ]);

        $validator->validate();
    }

    private function prepHandler($qry, $data, MediaModel $mediaModel, $config, $collection): void
    {
        if (data_get($data, 'title', false)) {
            $qry->usingName(data_get($data, 'title'));
        }

        if (! data_get($config, 'multiple', true)) {
            $mediaModel->clearMediaCollection($collection);
        }
    }

    private function executeHandler($handler, $collection)
    {
        return $handler->addCustomHeaders([
            'ACL' => 'public-read',
        ])
        ->toMediaCollection($collection);
    }

    private function saveFileCoordinates($media, $request)
    {
        $media->lat = $request->lat;
        $media->lng = $request->lng;
        $media->save();
    }

    private function saveAdditionalFileProperties($media, $request, $config)
    {
        $media->file_type = $this->getType($media, $config);
        $media->is_public = $request->is_public ?? true;
        $media->save();
    }

    private function getType($media, $config)
    {
        $documents = ['application/pdf', 'application/vnd.ms-excel', 'text/plain', 'application/msword'];
        $images = ['image/png', 'image/jpeg', 'image/svg+xml'];

        if (in_array($media->mime_type, $documents)) {
            return 'documents';
        }

        if (in_array($media->mime_type, $images)) {
            return 'media';
        }

        return  config('wri.file-handling.validation-file-types.' . data_get($config, 'validation', ''), '');
    }
}
