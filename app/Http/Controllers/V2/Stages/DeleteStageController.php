<?php

namespace App\Http\Controllers\V2\Stages;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Stages\StageResource;
use App\Models\V2\Stages\Stage;

class DeleteStageController extends Controller
{
    public function __invoke(Stage $stage): StageResource
    {
        $stage->forms()->delete();
        $stage->delete();

        return new StageResource($stage);
    }
}
