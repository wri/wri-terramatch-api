<?php

namespace App\Jobs;

use App\Helpers\ControllerHelper;
use App\Helpers\DraftHelper;
use App\Http\Requests\Terrafund\StoreTerrafundFileRequest;
use App\Http\Requests\Terrafund\StoreTerrafundProgrammeSubmissionRequest;
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

class PublishDraftTerrafundProgrammeSubmissionJob
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
        if ($this->draft->type != 'terrafund_programme_submission') {
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
            $dataArray['terrafund_programme_submission']['terrafund_due_submission_id'] = data_get($this->draft, 'terrafund_due_submission_id', null);
            $programmeSubmission = ControllerHelper::callAction('Terrafund\\TerrafundProgrammeSubmissionController@createAction', $dataArray['terrafund_programme_submission'], new StoreTerrafundProgrammeSubmissionRequest());

            if (count($dataArray['photos']) > 0) {
                foreach ($dataArray['photos'] as $photo) {
                    ControllerHelper::callAction('Terrafund\\TerrafundFileController@createAction', [
                        'fileable_type' => 'programme_submission',
                        'fileable_id' => $programmeSubmission->data->id,
                        'upload' => $photo['upload'],
                        'is_public' => $photo['is_public'],
                        'location_long' => $photo['location_long'] ?? null,
                        'location_lat' => $photo['location_lat'] ?? null,
                        'collection' => 'photos',
                    ], new StoreTerrafundFileRequest());
                }
            }
            if (isset($dataArray['other_additional_documents'])) {
                foreach ($dataArray['other_additional_documents'] as $document) {
                    ControllerHelper::callAction('Terrafund\\TerrafundFileController@createAction', [
                        'fileable_type' => 'programme_submission',
                        'fileable_id' => $programmeSubmission->data->id,
                        'upload' => $document['upload'],
                        'is_public' => data_get($document, 'is_public', true),
                        'location_long' => $document['location_long'] ?? null,
                        'location_lat' => $document['location_lat'] ?? null,
                        'collection' => 'other_additional_documents',
                    ], new StoreTerrafundFileRequest());
                }
            }
            if ($this->draft->terrafund_due_submission_id != null) {
                $this->draft->terrafundDueSubmission->update(['is_submitted' => true]);
            }
            $this->draft->delete();
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
