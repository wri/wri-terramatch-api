<?php

namespace App\Http\Controllers\V2\Applications;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Applications\ApplicationResource;
use App\Models\V2\Forms\Application;
use Illuminate\Http\Request;

class AdminViewApplicationController extends Controller
{
    public function __invoke(Request $request, Application $application): ApplicationResource
    {
        $this->authorize('readAll', Application::class);

        return new ApplicationResource($application);
    }
}
