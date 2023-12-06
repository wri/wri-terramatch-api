<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\SiteRestorationMethod;
use App\Resources\SiteRestorationMethodResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteRestorationMethodsController extends Controller
{
    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $siteRestorationMethods = SiteRestorationMethod::get();
        $resources = [];
        foreach ($siteRestorationMethods as $siteRestorationMethod) {
            $resources[] = new SiteRestorationMethodResource($siteRestorationMethod);
        }

        return JsonResponseHelper::success($resources, 200);
    }
}
