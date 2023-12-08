<?php

namespace App\Http\Controllers\V2\Applications;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Forms\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDeleteApplicationController extends Controller
{
    public function __invoke(Request $request, Application $application): JsonResponse
    {
        $this->authorize('delete', Application::class);

        foreach ($application->formSubmissions as $formSubmission) {
            $formSubmission->delete();
        }

        $application->delete();

        return JsonResponseHelper::success(['Application and it\'s submissions have been deleted.'], 200);
    }
}
