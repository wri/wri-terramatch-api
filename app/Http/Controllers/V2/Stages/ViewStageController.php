<?php

namespace App\Http\Controllers\V2\Stages;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Stages\StageResource;
use App\Models\V2\Stages\Stage;

class ViewStageController extends Controller
{
    public function __invoke(Stage $stage): StageResource
    {
        return new StageResource($stage);
    }
}
