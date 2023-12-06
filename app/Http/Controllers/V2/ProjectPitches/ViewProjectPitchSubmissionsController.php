<?php

namespace App\Http\Controllers\V2\ProjectPitches;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormSubmissionsCollection;
use App\Models\V2\ProjectPitch;

class ViewProjectPitchSubmissionsController extends Controller
{
    public function __invoke(ProjectPitch $projectPitch): FormSubmissionsCollection
    {
        $this->authorize('read', $projectPitch->organisation);

        $collection = $projectPitch->formSubmissions;

        return new FormSubmissionsCollection($collection);
    }
}
