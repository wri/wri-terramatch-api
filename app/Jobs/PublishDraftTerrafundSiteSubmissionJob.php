<?php

namespace App\Jobs;

use App\Helpers\ControllerHelper;
use App\Helpers\DraftHelper;
use App\Http\Controllers\Terrafund\TerrafundTreeSpeciesController;
use App\Http\Requests\Terrafund\StoreTerrafundCsvImportRequest;
use App\Http\Requests\Terrafund\StoreTerrafundDisturbanceRequest;
use App\Http\Requests\Terrafund\StoreTerrafundFileRequest;
use App\Http\Requests\Terrafund\StoreTerrafundNonTreeSpeciesRequest;
use App\Http\Requests\Terrafund\StoreTerrafundSiteSubmissionRequest;
use App\Http\Requests\Terrafund\StoreTerrafundTreeSpeciesRequest;
use App\Models\Draft as DraftModel;
use App\Models\V2\User as UserModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublishDraftTerrafundSiteSubmissionJob
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
        if ($this->draft->type != 'terrafund_site_submission') {
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
            $dataArray['terrafund_site_submission']['terrafund_due_submission_id'] = data_get($this->draft, 'terrafund_due_submission_id', null);
            $siteSubmission = ControllerHelper::callAction('Terrafund\\TerrafundSiteSubmissionController@createAction', $dataArray['terrafund_site_submission'], new StoreTerrafundSiteSubmissionRequest());
            if (count($dataArray['photos']) > 0) {
                foreach ($dataArray['photos'] as $photo) {
                    ControllerHelper::callAction('Terrafund\\TerrafundFileController@createAction', [
                        'fileable_type' => 'site_submission',
                        'fileable_id' => $siteSubmission->data->id,
                        'upload' => $photo['upload'],
                        'is_public' => $photo['is_public'],
                        'location_long' => $photo['location_long'] ?? null,
                        'location_lat' => $photo['location_lat'] ?? null,
                    ], new StoreTerrafundFileRequest());
                }
            }
            if (count($dataArray['tree_species']) > 0) {
                foreach ($dataArray['tree_species'] as $treeSpeciesData) {
                    $payload = [
                        'treeable_type' => 'site_submission',
                        'treeable_id' => $siteSubmission->data->id,
                        'name' => $treeSpeciesData['name'],
                        'amount' => $treeSpeciesData['amount'],
                    ];
                    $controller = new TerrafundTreeSpeciesController();
                    $controller->callAction('createAction', [new StoreTerrafundTreeSpeciesRequest($payload)]);
                }
            } else {
                $file = Arr::first($uploads, function ($upload) use ($dataArray) {
                    return $upload->id == $dataArray['tree_species_csv'];
                });
                if ($file) {
                    ControllerHelper::callAction('Terrafund\\TerrafundCsvImportController@createAction', [
                        'treeable_type' => 'site_submission',
                        'treeable_id' => $siteSubmission->data->id,
                        'upload_id' => $dataArray['tree_species_csv'],
                    ], new StoreTerrafundCsvImportRequest());
                }
            }
            if (count($dataArray['non_tree_species']) > 0) {
                foreach ($dataArray['non_tree_species'] as $nonTreeSpeciesData) {
                    ControllerHelper::callAction('Terrafund\\TerrafundNoneTreeSpeciesController@createAction', [
                        'speciesable_type' => 'site_submission',
                        'speciesable_id' => $siteSubmission->data->id,
                        'name' => $nonTreeSpeciesData['name'],
                        'amount' => $nonTreeSpeciesData['amount'],
                    ], new StoreTerrafundNonTreeSpeciesRequest());
                }
            }
            if (count($dataArray['disturbances']) > 0) {
                foreach ($dataArray['disturbances'] as $disturbanceData) {
                    ControllerHelper::callAction('Terrafund\\TerrafundDisturbanceController@createAction', [
                        'disturbanceable_type' => 'site_submission',
                        'disturbanceable_id' => $siteSubmission->data->id,
                        'type' => $disturbanceData['type'],
                        'description' => $disturbanceData['description'],
                    ], new StoreTerrafundDisturbanceRequest());
                }
            }
            if ($this->draft->terrafund_due_submission_id != null) {
                $this->draft->terrafundDueSubmission->update(['is_submitted' => true]);
            }

            $this->draft->delete();
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
