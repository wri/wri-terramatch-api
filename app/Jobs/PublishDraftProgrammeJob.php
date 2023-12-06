<?php

namespace App\Jobs;

use App\Helpers\ControllerHelper;
use App\Helpers\DraftHelper;
use App\Http\Requests\StoreDocumentFileRequest;
use App\Http\Requests\StoreProgrammeBoundaryRequest;
use App\Http\Requests\StoreProgrammeRequest;
use App\Http\Requests\StoreProgrammeTreeSpeciesCsvRequest;
use App\Http\Requests\StoreProgrammeTreeSpeciesRequest;
use App\Http\Requests\UpdateAimsRequest;
use App\Models\Draft as DraftModel;
use App\Models\User as UserModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublishDraftProgrammeJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $user;

    private $draft;

    public function __construct(UserModel $user, DraftModel $draft)
    {
        $this->user = $user;
        $this->draft = $draft;
    }

    public function handle()
    {
        if ($this->draft->type != 'programme') {
            throw new Exception();
        }
        $key = 'publish_draft_' . $this->draft->id;
        if (Cache::has($key)) {
            throw new ModelNotFoundException();
        }
        Cache::put($key, true, 3600);

        try {
            DB::beginTransaction();
            $dataArray = json_decode($this->draft->data, true);
            $dataObject = json_decode($this->draft->data);
            $uploads = DraftHelper::drafting($this->draft->type)::extractUploads($dataObject);
            $programme = ControllerHelper::callAction('ProgrammeController@createAction', $dataArray['programme'], new StoreProgrammeRequest());
            $dataArray['boundary']['programme_id'] = $programme->data->id;
            ControllerHelper::callAction('ProgrammeController@addBoundaryToProgrammeAction', $dataArray['boundary'], new StoreProgrammeBoundaryRequest());
            $dataArray['aims']['programme_id'] = $programme->data->id;
            ControllerHelper::callAction('AimController@updateAction', $dataArray['aims'], new UpdateAimsRequest());
            if (count($dataArray['programme_tree_species']) > 0) {
                foreach ($dataArray['programme_tree_species'] as $programmeTreeSpeciesData) {
                    ControllerHelper::callAction('ProgrammeTreeSpeciesController@createAction', ['programme_id' => $programme->data->id, 'name' => $programmeTreeSpeciesData], new StoreProgrammeTreeSpeciesRequest());
                }
            } else {
                $file = Arr::first($uploads, function ($upload) use ($dataArray) {
                    return $upload->id == $dataArray['programme_tree_species_file'];
                });
                if ($file) {
                    ControllerHelper::callAction('ProgrammeTreeSpeciesCsvController@createAction', ['programme_id' => $programme->data->id, 'file' => $file], new StoreProgrammeTreeSpeciesCsvRequest());
                }
            }
            if (! empty($dataArray['additional_tree_species'])) {
                ControllerHelper::callAction('DocumentFileController@createAction', [
                    'document_fileable_id' => $programme->data->id,
                    'document_fileable_type' => 'programme',
                    'upload' => $dataArray['additional_tree_species'],
                    'is_public' => false,
                    'title' => 'Additional Tree Species',
                    'collection' => 'tree_species',
                ], new StoreDocumentFileRequest());
            }
            if (count($dataArray['document_files']) > 0) {
                foreach ($dataArray['document_files'] as $documentFile) {
                    ControllerHelper::callAction('DocumentFileController@createAction', [
                        'document_fileable_id' => $programme->data->id,
                        'document_fileable_type' => 'programme',
                        'upload' => data_get($documentFile, 'upload', ''),
                        'is_public' => data_get($documentFile, 'is_public', false),
                        'title' => data_get($documentFile, 'title', ''),
                        'collection' => data_get($documentFile, 'collection', 'general'),
                    ], new StoreDocumentFileRequest());
                }
            }
            $this->draft->delete();
            DB::commit();
            Cache::forget($key);

            return $programme->data->id;
        } catch (Exception $exception) {
            DB::rollBack();
            Cache::forget($key);

            throw $exception;
        }
    }
}
