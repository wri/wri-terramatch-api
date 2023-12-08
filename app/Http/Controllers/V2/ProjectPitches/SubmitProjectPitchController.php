<?php

namespace App\Http\Controllers\V2\ProjectPitches;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectPitches\ProjectPitchResource;
use App\Http\Validators\ProjectPitchSubmitValidation;
use App\Models\V2\ProjectPitch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubmitProjectPitchController extends Controller
{
    public function __invoke(ProjectPitch $projectPitch, Request $request): ProjectPitchResource
    {
        $this->authorize('submit', $projectPitch);

        $validator = Validator::make($projectPitch->toArray(), (new ProjectPitchSubmitValidation())->rules());
        $validator->validate();

        $projectPitch->status = ProjectPitch::STATUS_ACTIVE;
        $projectPitch->save();


        return new ProjectPitchResource($projectPitch);
    }
}
