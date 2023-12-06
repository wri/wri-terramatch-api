<?php

namespace App\Jobs;

use App\Helpers\ControllerHelper;
use App\Helpers\DraftHelper;
use App\Http\Requests\StoreDocumentFileRequest;
use App\Http\Requests\StoreProgrammeSubmissionRequest;
use App\Http\Requests\StoreProgrammeTreeSpeciesRequest;
use App\Http\Requests\StoreSocioeconomicBenefitsRequest;
use App\Http\Requests\StoreSubmissionMediaUploadRequest;
use App\Models\Draft as DraftModel;
use App\Models\DueSubmission;
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

class PublishDraftProgrammeSubmissionJob
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
        if ($this->draft->type != 'programme_submission') {
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
            $programmeSubmission = ControllerHelper::callAction('SubmissionController@createAction', [
                'programme_id' => $dataArray['programme_submission']['programme_id'],
                'title' => $dataArray['programme_submission']['title'],
                'workdays_paid' => data_get($dataArray, 'workdays_paid', null),
                'workdays_volunteer' => data_get($dataArray, 'workdays_volunteer', null),
                'due_submission_id' => $this->draft->due_submission_id,
                'created_by' => $dataArray['programme_submission']['created_by'],
                'technical_narrative' => $dataArray['narratives']['technical_narrative'],
                'public_narrative' => $dataArray['narratives']['public_narrative'],
            ], new StoreProgrammeSubmissionRequest());
            if (isset($dataArray['socioeconomic_benefits']) && ! is_null($dataArray['socioeconomic_benefits'])) {
                ControllerHelper::callAction('SocioeconomicBenefitsController@uploadAction', [
                    'programme_submission_id' => $programmeSubmission->data->id,
                    'programme_id' => $dataArray['programme_submission']['programme_id'],
                    'upload' => $dataArray['socioeconomic_benefits'],
                    'name' => 'Socioeconomic Benefits',
                ], new StoreSocioeconomicBenefitsRequest());
            }
            if (! empty($dataArray['additional_tree_species'])) {
                ControllerHelper::callAction('DocumentFileController@createAction', [
                    'document_fileable_id' => $programmeSubmission->data->id,
                    'document_fileable_type' => 'submission',
                    'upload' => $dataArray['additional_tree_species'],
                    'is_public' => false,
                    'title' => 'Additional Tree Species',
                    'collection' => 'tree_species',
                ], new StoreDocumentFileRequest());
            }
            if (count($dataArray['programme_tree_species']) > 0) {
                foreach ($dataArray['programme_tree_species'] as $programmeSubmissionTreeSpeciesData) {
                    $programmeSubmissionTreeSpeciesData['submission_id'] = $programmeSubmission->data->id;
                    $programmeSubmissionTreeSpeciesData['programme_id'] = $dataArray['programme_submission']['programme_id'];
                    ControllerHelper::callAction('ProgrammeTreeSpeciesController@createAction', [
                        'programme_id' => $programmeSubmissionTreeSpeciesData['programme_id'],
                        'programme_submission_id' => $programmeSubmission->data->id,
                        'name' => $programmeSubmissionTreeSpeciesData['name'],
                        'amount' => $programmeSubmissionTreeSpeciesData['amount'],
                    ], new StoreProgrammeTreeSpeciesRequest());
                }
            } else {
                $file = Arr::first($uploads, function ($upload) use ($dataArray) {
                    return $upload->id == $dataArray['programme_tree_species_file'];
                });
                if ($file) {
                    ControllerHelper::callAction('ProgrammeTreeSpeciesCsvController@createAction', [
                        'programme_id' => $dataArray['programme_submission']['programme_id'],
                        'programme_submission_id' => $programmeSubmission->data->id,
                        'file' => $file,
                    ]);
                }
            }
            if (! is_null($dataArray['media'])) {
                foreach ($dataArray['media'] as $media) {
                    ControllerHelper::callAction('SubmissionMediaUploadController@createAction', [
                        'submission_id' => $programmeSubmission->data->id,
                        'upload' => $media['upload'],
                        'is_public' => $media['is_public'],
                        'location_long' => isset($media['location_long']) ? $media['location_long'] : null,
                        'location_lat' => isset($media['location_lat']) ? $media['location_lat'] : null,
                    ], new StoreSubmissionMediaUploadRequest());
                }
            }
            if (count($dataArray['document_files']) > 0) {
                foreach ($dataArray['document_files'] as $documentFile) {
                    ControllerHelper::callAction('DocumentFileController@createAction', [
                        'document_fileable_id' => $programmeSubmission->data->id,
                        'document_fileable_type' => 'submission',
                        'upload' => data_get($documentFile, 'upload', ''),
                        'is_public' => data_get($documentFile, 'is_public', false),
                        'title' => data_get($documentFile, 'title', ''),
                        'collection' => data_get($documentFile, 'collection', 'general'),
                    ], new StoreDocumentFileRequest());
                }
            }
            if ($this->draft->due_submission_id) {
                $dueSubmission = DueSubmission::where('id', $this->draft->due_submission_id)->firstOrFail();
                $dueSubmission->is_submitted = true;
                $dueSubmission->saveOrFail();
                $this->draft->delete();
            }
            DB::commit();
            Cache::forget($key);

            return $programmeSubmission->data->id;
        } catch (Exception $exception) {
            DB::rollBack();
            Cache::forget($key);

            throw $exception;
        }
    }
}
