<?php

namespace App\Http\Controllers\V2\Files;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\File\UploadRequest;
use App\Http\Resources\V2\Files\FileResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use mysql_xdevapi\Exception;

class UploadController extends Controller
{
    public function __invoke(UploadRequest $request, $model, $collection, $uuid)
    {
        $entity = $this->getEntity($model, $uuid);
        $this->authorize('uploadFiles', $entity);
        $config = $this->getConfiguration($entity, $collection);
        $this->validateFile($request, $config);

        $qry = $entity->addMediaFromRequest('upload_file');
        $this->prepHandler($qry, $request->all(), $entity, $config, $collection);
        $details = $this->executeHandler($qry, $collection);

        if (Arr::has($request->all(), ['lat', 'lng'])) {
            $this->saveFileCoordinates($details, $request);
        }

        $this->saveAdditionalFileProperties($details, $request, $config);

        return new FileResource($details);
    }

    private function getEntity($model, $uuid): Model
    {
        switch ($model) {
            case 'organisation':
                $entity = Organisation::isUuid($uuid)->first();

                break;
            case 'project-pitch':
                $entity = ProjectPitch::isUuid($uuid)->first();

                break;
            case 'funding-programme':
                $entity = FundingProgramme::isUuid($uuid)->first();

                break;
            case 'form':
                $entity = Form::isUuid($uuid)->first();

                break;
            case 'form-question-option':
                $entity = FormQuestionOption::isUuid($uuid)->first();

                break;
            case 'project':
                $entity = Project::isUuid($uuid)->first();

                break;
            case 'site':
                $entity = Site::isUuid($uuid)->first();

                break;
            case 'nursery':
                $entity = Nursery::isUuid($uuid)->first();

                break;
            case 'project-report':
                $entity = ProjectReport::isUuid($uuid)->first();

                break;
            case 'site-report':
                $entity = SiteReport::isUuid($uuid)->first();

                break;
            case 'nursery-report':
                $entity = NurseryReport::isUuid($uuid)->first();

                break;
            case 'project-monitoring':
                $entity = ProjectMonitoring::isUuid($uuid)->first();

                break;
            case 'site-monitoring':
                $entity = SiteMonitoring::isUuid($uuid)->first();

                break;
        }

        if (empty($entity)) {
            throw new ModelNotFoundException();
        }

        return $entity;
    }

    private function getConfiguration($entity, $collection): array
    {
        $config = $entity->fileConfiguration[$collection];

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

    private function prepHandler($qry, $data, $entity, $config, $collection): void
    {
        if (data_get($data, 'title', false)) {
            $qry->usingName(data_get($data, 'title'));
        }

        if (! data_get($config, 'multiple', true)) {
            $entity->clearMediaCollection($collection);
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
