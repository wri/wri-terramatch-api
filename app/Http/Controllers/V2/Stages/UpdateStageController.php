<?php

namespace App\Http\Controllers\V2\Stages;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Stages\UpdateStageRequest;
use App\Http\Resources\V2\Stages\StageResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Stages\Stage;
use Carbon\Carbon;

class UpdateStageController extends Controller
{
    public function __invoke(Stage $stage, UpdateStageRequest $updateStageRequest): StageResource
    {
        $data = array_filter($updateStageRequest->validated(), fn ($i) => ! is_null($i));

        if (data_get($data, 'deadline_at')) {
            data_set($data, 'deadline_at', Carbon::createFromFormat('Y-m-d H:i:s', data_get($data, 'deadline_at'), 'EST'));
        }

        $stage->update($data);

        if ($updateStageRequest->get('form_id')) {
            //clear old form link
            Form::where('stage_id', $stage->uuid)->update(['stage_id' => null]);

            //create new link
            $form = Form::isUuid($updateStageRequest->get('form_id'))->first();
            $form->update([
                'stage_id' => $stage->uuid,
            ]);
        }

        return new StageResource($stage);
    }
}
