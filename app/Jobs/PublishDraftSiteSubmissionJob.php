<?php

namespace App\Jobs;

use App\Helpers\ControllerHelper;
use App\Helpers\DraftHelper;
use App\Http\Controllers\SiteTreeSpeciesController;
use App\Http\Requests\StoreDirectSeedingRequest;
use App\Http\Requests\StoreDocumentFileRequest;
use App\Http\Requests\StoreSiteSubmissionRequest;
use App\Http\Requests\StoreSiteTreeSpeciesRequest;
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

class PublishDraftSiteSubmissionJob
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
        if ($this->draft->type != 'site_submission') {
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
            $siteSubmission = ControllerHelper::callAction('SiteSubmissionController@createAction', [
                'site_id' => $dataArray['site_submission']['site_id'],
                'created_by' => $dataArray['site_submission']['created_by'],
                'workdays_paid' => data_get($dataArray, 'workdays_paid', null),
                'workdays_volunteer' => data_get($dataArray, 'workdays_volunteer', null),
                'direct_seeding_kg' => $dataArray['direct_seeding']['direct_seeding_kg'],
                'due_submission_id' => $this->draft->due_submission_id,
                'technical_narrative' => ! empty($dataArray['narratives']) ? data_get($dataArray['narratives'], 'technical_narrative', '') : '',
                'public_narrative' => ! empty($dataArray['narratives']) ? data_get($dataArray['narratives'], 'public_narrative', '') : '',
            ], new StoreSiteSubmissionRequest());
            if (! empty($dataArray['socioeconomic_benefits'])) {
                ControllerHelper::callAction('SocioeconomicBenefitsController@uploadAction', [
                    'site_id' => $dataArray['site_submission']['site_id'],
                    'site_submission_id' => $siteSubmission->data->id,
                    'upload' => $dataArray['socioeconomic_benefits'],
                    'name' => 'Socioeconomic Benefits',
                ], new StoreSocioeconomicBenefitsRequest());
            }
            if (count($dataArray['site_tree_species']) > 0) {
                foreach ($dataArray['site_tree_species'] as $siteSubmissionTreeSpeciesData) {
                    $siteSubmissionTreeSpeciesData['site_submission_id'] = $siteSubmission->data->id;
                    $siteSubmissionTreeSpeciesData['site_id'] = $dataArray['site_submission']['site_id'];
                    $controller = new SiteTreeSpeciesController();
                    $controller->callAction('createAction', [new StoreSiteTreeSpeciesRequest($siteSubmissionTreeSpeciesData)]);
                }
            } else {
                $file = Arr::first($uploads, function ($upload) use ($dataArray) {
                    return $upload->id == $dataArray['site_tree_species_file'];
                });
                if ($file) {
                    ControllerHelper::callAction('SiteTreeSpeciesCsvController@createAction', [
                        'site_submission_id' => $siteSubmission->data->id,
                        'site_id' => $dataArray['site_submission']['site_id'],
                        'file' => $file,
                    ]);
                }
            }
            foreach ($dataArray['direct_seeding']['kg_by_species'] as $kgBySpecies) {
                ControllerHelper::callAction('DirectSeedingController@createAction', [
                    'site_submission_id' => $siteSubmission->data->id,
                    'name' => $kgBySpecies['name'],
                    'weight' => $kgBySpecies['weight'],
                ], new StoreDirectSeedingRequest());
            }
            if (! is_null($dataArray['media'])) {
                foreach ($dataArray['media'] as $media) {
                    ControllerHelper::callAction('SubmissionMediaUploadController@createAction', [
                        'site_submission_id' => $siteSubmission->data->id,
                        'upload' => $media['upload'],
                        'is_public' => $media['is_public'],
                        'location_long' => isset($media['location_long']) ? $media['location_long'] : null,
                        'location_lat' => isset($media['location_lat']) ? $media['location_lat'] : null,
                    ], new StoreSubmissionMediaUploadRequest());
                }
            }
            if (! empty($dataArray['additional_tree_species'])) {
                ControllerHelper::callAction('DocumentFileController@createAction', [
                    'document_fileable_id' => $siteSubmission->data->id,
                    'document_fileable_type' => 'site_submission',
                    'upload' => $dataArray['additional_tree_species'],
                    'is_public' => false,
                    'title' => 'Additional Tree Species',
                    'collection' => 'tree_species',
                ], new StoreDocumentFileRequest());
            }
            if (count($dataArray['document_files']) > 0) {
                foreach ($dataArray['document_files'] as $documentFile) {
                    ControllerHelper::callAction('DocumentFileController@createAction', [
                        'document_fileable_id' => $siteSubmission->data->id,
                        'document_fileable_type' => 'site_submission',
                        'upload' => data_get($documentFile, 'upload', ''),
                        'is_public' => data_get($documentFile, 'is_public', false),
                        'title' => data_get($documentFile, 'title', ''),
                        'collection' => data_get($documentFile, 'collection', 'general'),
                    ], new StoreDocumentFileRequest());
                }
            }
            if (! is_null($dataArray['disturbances'])) {
                foreach ($dataArray['disturbances'] as $disturbance) {
                    ControllerHelper::callAction('SiteSubmissionDisturbanceController@createAction', [
                        'site_submission_id' => $siteSubmission->data->id,
                        'disturbance_type' => $disturbance['disturbance_type'],
                        'extent' => $disturbance['extent'],
                        'intensity' => $disturbance['intensity'],
                        'description' => isset($disturbance['description']) ? $disturbance['description'] : null,
                    ]);
                }
            }
            if (! is_null($dataArray['disturbance_information'])) {
                ControllerHelper::callAction('SiteSubmissionDisturbanceController@createDisturbanceInformationAction', [
                    'site_submission_id' => $siteSubmission->data->id,
                    'disturbance_information' => $dataArray['disturbance_information'],
                ]);
            }
            if ($this->draft->due_submission_id) {
                $dueSubmission = DueSubmission::where('id', $this->draft->due_submission_id)->firstOrFail();
                $dueSubmission->is_submitted = true;
                $dueSubmission->saveOrFail();
                $this->draft->delete();
            }
            DB::commit();
            Cache::forget($key);

            return $siteSubmission->data->id;
        } catch (Exception $exception) {
            DB::rollBack();
            Cache::forget($key);

            throw $exception;
        }
    }
}
