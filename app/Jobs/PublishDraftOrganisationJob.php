<?php

namespace App\Jobs;

use App\Helpers\ControllerHelper;
use App\Http\Requests\StoreOrganisationFileRequest;
use App\Http\Requests\StoreOrganisationPhotoRequest;
use App\Http\Requests\StoreOrganisationRequest;
use App\Models\Draft as DraftModel;
use App\Models\User as UserModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublishDraftOrganisationJob
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
        if ($this->draft->type != 'organisation') {
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
            $organisation = ControllerHelper::callAction('OrganisationsController@createAction', $dataArray['organisation'], new StoreOrganisationRequest());
            if (count($dataArray['photos']) > 0) {
                foreach ($dataArray['photos'] as $photo) {
                    ControllerHelper::callAction('OrganisationPhotoController@createAction', [
                        'organisation_id' => $organisation->data->data->id,
                        'upload' => $photo['upload'],
                        'is_public' => $photo['is_public'],
                    ], new StoreOrganisationPhotoRequest());
                }
            }
            if (count($dataArray['files']) > 0) {
                foreach ($dataArray['files'] as $file) {
                    ControllerHelper::callAction('OrganisationFileController@createAction', [
                        'organisation_id' => $organisation->data->data->id,
                        'upload' => $file['upload'],
                        'type' => $file['type'],
                    ], new StoreOrganisationFileRequest());
                }
            }
            $this->draft->delete();
            DB::commit();
            Cache::forget($key);

            return $organisation->data->id;
        } catch (Exception $exception) {
            DB::rollBack();
            Cache::forget($key);

            throw $exception;
        }
    }
}
