<?php

namespace App\Jobs;

use App\Helpers\ControllerHelper;
use App\Helpers\DraftHelper;
use App\Models\Draft as DraftModel;
use App\Models\User as UserModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublishDraftPitchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $draft;

    public function __construct(UserModel $user, DraftModel $draft)
    {
        $this->user = $user;
        $this->draft = $draft;
    }

    public function handle()
    {
        if ($this->draft->type != "pitch") {
            throw new Exception();
        }
        $key = "publish_draft_" . $this->draft->id;
        if (Cache::has($key)) {
            throw new ModelNotFoundException();
        }
        Cache::put($key, true, 3600);
        try {
            DB::beginTransaction();
            $dataArray = json_decode($this->draft->data, true);
            $dataObject = json_decode($this->draft->data);
            $uploads = DraftHelper::extractUploads($this->draft->type, $dataObject);
            foreach ($uploads as $upload) {
                $upload->user_id = $this->user->id;
                $upload->save();
            }
            $pitch = ControllerHelper::callAction("PitchesController@createAction", $dataArray["pitch"]);
            foreach ($dataArray["pitch_contacts"] as $pitchContactsDatum) {
                $pitchContactsDatum["pitch_id"] = $pitch->data->data->id;
                ControllerHelper::callAction("PitchContactsController@createAction", $pitchContactsDatum);
            }
            foreach ($dataArray["pitch_documents"] as $pitchDocumentsDatum) {
                $pitchDocumentsDatum["pitch_id"] = $pitch->data->data->id;
                ControllerHelper::callAction("PitchDocumentsController@createAction", $pitchDocumentsDatum);
            }
            foreach ($dataArray["carbon_certifications"] as $carbonCertificationsDatum) {
                $carbonCertificationsDatum["pitch_id"] = $pitch->data->data->id;
                ControllerHelper::callAction("CarbonCertificationsController@createAction", $carbonCertificationsDatum);
            }
            foreach ($dataArray["restoration_method_metrics"] as $restorationMethodMetricsDatum) {
                $restorationMethodMetricsDatum["pitch_id"] = $pitch->data->data->id;
                ControllerHelper::callAction("RestorationMethodMetricsController@createAction", $restorationMethodMetricsDatum);
            }
            foreach ($dataArray["tree_species"] as $treeSpeciesDatum) {
                $treeSpeciesDatum["pitch_id"] = $pitch->data->data->id;
                ControllerHelper::callAction("TreeSpeciesController@createAction", $treeSpeciesDatum);
            }
            $this->draft->delete();
            DB::commit();
            Cache::forget($key);
            return $pitch->data->data->id;
        } catch (Exception $exception) {
            DB::rollBack();
            Cache::forget($key);
            throw $exception;
        }
    }
}
