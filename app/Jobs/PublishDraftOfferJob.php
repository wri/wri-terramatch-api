<?php

namespace App\Jobs;

use App\Helpers\ControllerHelper;
use App\Helpers\DraftHelper;
use App\Http\Requests\StoreOfferContactsRequest;
use App\Http\Requests\StoreOfferDocumentsRequest;
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

class PublishDraftOfferJob
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
        if ($this->draft->type != 'offer') {
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
            foreach ($uploads as $upload) {
                $upload->user_id = $this->user->id;
                $upload->saveOrFail();
            }
            $offer = ControllerHelper::callAction('OffersController@createAction', $dataArray['offer']);
            foreach ($dataArray['offer_contacts'] as $offerContactsDatum) {
                $offerContactsDatum['offer_id'] = $offer->data->id;
                ControllerHelper::callAction('OfferContactsController@createAction', $offerContactsDatum, new StoreOfferContactsRequest());
            }
            foreach ($dataArray['offer_documents'] as $offerDocumentsDatum) {
                $offerDocumentsDatum['offer_id'] = $offer->data->id;
                ControllerHelper::callAction('OfferDocumentsController@createAction', $offerDocumentsDatum, new StoreOfferDocumentsRequest());
            }
            $this->draft->delete();
            DB::commit();
            Cache::forget($key);

            return $offer->data->id;
        } catch (Exception $exception) {
            DB::rollBack();
            Cache::forget($key);

            throw $exception;
        }
    }
}
