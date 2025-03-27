<?php

namespace App\Http\Controllers\V2\Files;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\File\BulkUploadRequest;
use App\Http\Requests\V2\File\UploadRequest;
use App\Http\Resources\V2\Files\FileResource;
use App\Models\V2\MediaModel;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $this->saveFileCoordinates($details, $request->all());
        $this->saveAdditionalFileProperties($details, $request->all(), $config);

        return new FileResource($details);
    }

    public function bulkUrlUpload(BulkUploadRequest $request, string $collection, MediaModel $mediaModel)
    {
        $this->authorize('uploadFiles', $mediaModel);

        if ($collection != 'photos') {
            // Only the photos collection is allowed for bulk upload
            throw new NotFoundHttpException();
        }

        $config = $this->getConfiguration($mediaModel, $collection);
        $files = [];

        try {
            foreach ($request->getPayload() as $data) {
                // The downloadable file gets shuttled through the internals of Spatie without a chance for us to run
                // our own validations on them. png/jpg are the only mimes allowed for the photos collection according
                // to config/file-handling.php, and we disallow other collections than 'photos' above.
                $handler = $mediaModel->addMediaFromUrl(
                    $data['download_url'],
                    'image/png',
                    'image/jpg',
                    'image/jpeg',
                    'image/heif',
                    'image/heic'
                );

                $this->prepHandler($handler, $data, $mediaModel, $config, $collection);
                $details = $this->executeHandler($handler, $collection);

                $this->saveFileCoordinates($details, $data);
                $this->saveAdditionalFileProperties($details, $data, $config);

                $files[] = $details;
            }
        } catch (Exception $exception) {
            // if we get an error in the bulk upload, remove any media that did successfully get saved.
            foreach ($files as $file) {
                $file->delete();
            }

            throw $exception;
        }

        return FileResource::collection($files);
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

    private function saveFileCoordinates($media, $data)
    {
        if (Arr::has($data, ['lat', 'lng'])) {
            $media->lat = $data['lat'];
            $media->lng = $data['lng'];
            $media->save();
        }
    }

    private function saveAdditionalFileProperties($media, $data, $config)
    {
        $media->file_type = $this->getType($media, $config);
        $media->is_public = $data['is_public'] ?? true;
        $media->created_by = Auth::user()->id;
        $media->photographer = Auth::user()->name;
        $media->save();
    }

    private function getType($media, $config)
    {
        $documents = ['application/pdf', 'application/vnd.ms-excel', 'text/plain', 'application/msword'];
        $images = ['image/png', 'image/jpeg', 'image/heif', 'image/heic', 'image/svg+xml'];
        $videos = ['video/mp4'];

        if (in_array($media->mime_type, $documents)) {
            return 'documents';
        }

        if (in_array($media->mime_type, $images) || in_array($media->mime_type, $videos)) {
            return 'media';
        }

        return config('wri.file-handling.validation-file-types.' . data_get($config, 'validation', ''), '');
    }
}
