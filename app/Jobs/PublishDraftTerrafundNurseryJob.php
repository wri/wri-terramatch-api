<?php

namespace App\Jobs;

use App\Exceptions\NoTreeSpeciesProvided;
use App\Helpers\ControllerHelper;
use App\Helpers\DraftHelper;
use App\Http\Controllers\Terrafund\TerrafundTreeSpeciesController;
use App\Http\Requests\Terrafund\StoreTerrafundCsvImportRequest;
use App\Http\Requests\Terrafund\StoreTerrafundFileRequest;
use App\Http\Requests\Terrafund\StoreTerrafundNurseryRequest;
use App\Http\Requests\Terrafund\StoreTerrafundTreeSpeciesRequest;
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

class PublishDraftTerrafundNurseryJob
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
        if ($this->draft->type != 'terrafund_nursery') {
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
            $nursery = ControllerHelper::callAction('Terrafund\\TerrafundNurseryController@createAction', $dataArray['terrafund_nursery'], new StoreTerrafundNurseryRequest());
            if (count($dataArray['tree_species']) > 0) {
                foreach ($dataArray['tree_species'] as $treeSpeciesData) {
                    $payload = [
                        'treeable_type' => 'nursery',
                        'treeable_id' => $nursery->data->id,
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
                        'treeable_type' => 'nursery',
                        'treeable_id' => $nursery->data->id,
                        'upload_id' => $dataArray['tree_species_csv'],
                    ], new StoreTerrafundCsvImportRequest());
                } else {
                    throw new NoTreeSpeciesProvided();
                }
            }
            if (count($dataArray['photos']) > 0) {
                foreach ($dataArray['photos'] as $photo) {
                    ControllerHelper::callAction('Terrafund\\TerrafundFileController@createAction', [
                        'fileable_type' => 'nursery',
                        'fileable_id' => $nursery->data->id,
                        'upload' => $photo['upload'],
                        'is_public' => $photo['is_public'],
                        'location_long' => $photo['location_long'] ?? null,
                        'location_lat' => $photo['location_lat'] ?? null,
                    ], new StoreTerrafundFileRequest());
                }
            }
            $this->draft->delete();
            DB::commit();
            Cache::forget($key);

            return $nursery->data->id;
        } catch (Exception $exception) {
            DB::rollBack();
            Cache::forget($key);

            throw $exception;
        }
    }
}
