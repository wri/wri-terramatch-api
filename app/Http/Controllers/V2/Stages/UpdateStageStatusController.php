<?php

namespace App\Http\Controllers\V2\Stages;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Stages\UpdateStageStatusRequest;
use App\Http\Resources\V2\Stages\StageResource;
use App\Models\V2\Stages\Stage;

class UpdateStageStatusController extends Controller
{
    public function __invoke(Stage $stage, UpdateStageStatusRequest $updateStageStatusRequest): StageResource
    {
        $stage->update($updateStageStatusRequest->validated());
        $stage->save();

        return new StageResource($stage);
    }
}
