<?php

namespace App\Jobs;

use App\Helpers\ControllerHelper;
use App\Helpers\DraftHelper;
use App\Http\Controllers\InvasiveController;
use App\Http\Controllers\SeedDetailController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteTreeSpeciesController;
use App\Http\Requests\StoreAimsRequest;
use App\Http\Requests\StoreControlAimsRequest;
use App\Http\Requests\StoreDocumentFileRequest;
use App\Http\Requests\StoreInvasivesRequest;
use App\Http\Requests\StoreMediaUploadRequest;
use App\Http\Requests\StoreSeedsRequest;
use App\Http\Requests\StoreSiteBoundaryRequest;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\StoreSiteTreeSpeciesRequest;
use App\Http\Requests\StoreSocioeconomicBenefitsRequest;
use App\Models\Draft as DraftModel;
use App\Models\Site;
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

class PublishDraftSiteJob
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
        if ($this->draft->type != 'site') {
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
            $siteResponse = ControllerHelper::callAction('SiteController@createAction', $dataArray['site'], new StoreSiteRequest());
            $site = Site::find($siteResponse->data->id);
            $siteController = new SiteController();

            if (! $site->control_site) {
                $dataArray['restoration_methods']['site_id'] = $site->id;
                ControllerHelper::callAction('SiteController@attachRestorationMethodsAction', $dataArray['restoration_methods']);

                ControllerHelper::callAction('SiteController@attachLandTenureAction', [
                    'land_tenure_ids' => $dataArray['land_tenure'],
                    'site_id' => $site->id,
                ]);
            }

            $dataArray['aims']['site_id'] = $site->id;
            if ($site->control_site) {
                $siteController->callAction('createControlAimAction', [new StoreControlAimsRequest($dataArray['aims']), $site]);
            } else {
                $siteController->callAction('createAimAction', [new StoreAimsRequest($dataArray['aims']), $site]);
            }

            $dataArray['establishment_date']['site_id'] = $site->id;
            ControllerHelper::callAction('SiteController@updateEstablishmentDateAction', $dataArray['establishment_date']);

            if (! empty($dataArray['boundary']) && ! empty($dataArray['boundary']['boundary_geojson'])) {
                $dataArray['boundary']['site_id'] = $site->id;
                $siteController->callAction('addBoundaryToSiteAction', [new StoreSiteBoundaryRequest($dataArray['boundary'])]);
            }

            if (! empty($dataArray['socioeconomic_benefits'])) {
                ControllerHelper::callAction('SocioeconomicBenefitsController@uploadAction', [
                    'site_id' => $site->id,
                    'upload' => $dataArray['socioeconomic_benefits'],
                    'name' => 'Socioeconomic Benefits',
                ], new StoreSocioeconomicBenefitsRequest());
            }

            if (! empty($dataArray['additional_tree_species'])) {
                ControllerHelper::callAction('DocumentFileController@createAction', [
                    'document_fileable_id' => $site->id,
                    'document_fileable_type' => 'site',
                    'upload' => $dataArray['additional_tree_species'],
                    'is_public' => false,
                    'title' => 'Additional Tree Species',
                    'collection' => 'tree_species',
                ], new StoreDocumentFileRequest());
            }

            if (count($dataArray['site_tree_species'])) {
                foreach ($dataArray['site_tree_species'] as $siteTreeSpeciesName) {
                    $controller = new SiteTreeSpeciesController();
                    $controller->callAction('createAction', [new StoreSiteTreeSpeciesRequest(['site_id' => $site->id, 'name' => $siteTreeSpeciesName]), $site]);
                }
            } else {
                $file = Arr::first($uploads, function ($upload) use ($dataArray) {
                    return $upload->id == $dataArray['site_tree_species_file'];
                });
                if ($file) {
                    ControllerHelper::callAction('SiteTreeSpeciesCsvController@createAction', ['site_id' => $site->id, 'file' => $file]);
                }
            }

            if (! is_null($dataArray['media'])) {
                foreach ($dataArray['media'] as $media) {
                    ControllerHelper::callAction('MediaUploadController@createAction', [
                        'site_id' => $site->id,
                        'upload' => $media['upload'],
                        'is_public' => $media['is_public'],
                        'location_long' => isset($media['location_long']) ? $media['location_long'] : null,
                        'location_lat' => isset($media['location_lat']) ? $media['location_lat'] : null,
                    ], new StoreMediaUploadRequest());
                }
            }
            if (! is_null($dataArray['document_files']) && count($dataArray['document_files']) > 0) {
                foreach ($dataArray['document_files'] as $documentFile) {
                    ControllerHelper::callAction('DocumentFileController@createAction', [
                        'document_fileable_id' => $site->id,
                        'document_fileable_type' => 'site',
                        'upload' => data_get($documentFile, 'upload', ''),
                        'is_public' => data_get($documentFile, 'is_public', false),
                        'title' => data_get($documentFile, 'title', ''),
                        'collection' => data_get($documentFile, 'collection', 'general'),
                    ], new StoreDocumentFileRequest());
                }
            }

            if (! is_null($dataArray['seeds'])) {
                foreach ($dataArray['seeds'] as $seed) {
                    $payload = [
                        'site_id' => $site->id,
                        'name' => $seed['name'],
                        'weight_of_sample' => $seed['weight_of_sample'],
                        'seeds_in_sample' => $seed['seeds_in_sample'],
                    ];
                    $controller = new SeedDetailController();
                    $controller->callAction('createAction', [new StoreSeedsRequest($payload)]);
                }
            }

            if (! is_null($dataArray['invasives'])) {
                foreach ($dataArray['invasives'] as $invasive) {
                    $payload = [
                        'site_id' => $site->id,
                        'name' => $invasive['name'],
                        'type' => $invasive['type'],
                    ];
                    $controller = new InvasiveController();
                    $controller->callAction('createAction', [new StoreInvasivesRequest($payload)]);
                }
            }

            $this->draft->delete();
            DB::commit();
            Cache::forget($key);

            return $site->id;
        } catch (Exception $exception) {
            DB::rollBack();
            Cache::forget($key);

            throw $exception;
        }
    }
}
