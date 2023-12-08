<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DefaultController extends Controller
{
    /**
     * This method returns an empty object to keep the load balancers happy that
     * the web server is healthy.
     */
    public function indexAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        return JsonResponseHelper::success((object) [], 200);
    }
}
