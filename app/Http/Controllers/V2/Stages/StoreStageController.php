<?php

namespace App\Http\Controllers\V2\Stages;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Stages\StoreStageRequest;
use App\Http\Resources\V2\Stages\StageResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Stages\Stage;
use Carbon\Carbon;

class StoreStageController extends Controller
{
    public function __invoke(StoreStageRequest $storeStageRequest): StageResource
    {
        $data = $storeStageRequest->validated();

        $stage = Stage::create([
            'name' => data_get($data, 'name'),
            'funding_programme_id' => data_get($data, 'funding_programme_id'),
            'deadline_at' => ! is_null(data_get($data, 'deadline_at')) ? Carbon::createFromFormat('Y-m-d H:i:s', data_get($data, 'deadline_at'), 'EST') : null,
            'order' => data_get($data, 'order'),
        ]);

        $frameworkKey = $stage->fundingProgramme->framework_key;

        if ($storeStageRequest->get('form_id')) {
            $form = Form::isUuid($storeStageRequest->get('form_id'))->first();
            $form->update([
                'stage_id' => $stage->uuid,
                'framework_key' => $frameworkKey,
            ]);
        }

        return new StageResource($stage);
    }
}
