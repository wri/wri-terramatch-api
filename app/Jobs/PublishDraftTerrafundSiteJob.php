<?php

namespace App\Jobs;

use App\Helpers\ControllerHelper;
use App\Helpers\DraftHelper;
use App\Http\Requests\Terrafund\StoreTerrafundFileRequest;
use App\Http\Requests\Terrafund\StoreTerrafundSiteRequest;
use App\Models\Draft as DraftModel;
use App\Models\V2\User as UserModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublishDraftTerrafundSiteJob
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
        if ($this->draft->type != 'terrafund_site') {
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
            $site = ControllerHelper::callAction('Terrafund\\TerrafundSiteController@createAction', $dataArray['terrafund_site'], new StoreTerrafundSiteRequest());
            if (count($dataArray['photos']) > 0) {
                foreach ($dataArray['photos'] as $photo) {
                    ControllerHelper::callAction('Terrafund\\TerrafundFileController@createAction', [
                        'fileable_type' => 'site',
                        'fileable_id' => $site->data->id,
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

            return $site->data->id;
        } catch (Exception $exception) {
            DB::rollBack();
            Cache::forget($key);

            throw $exception;
        }
    }
}
