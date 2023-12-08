<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\LandTenure;
use App\Resources\LandTenureResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandTenureController extends Controller
{
    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $landTenures = LandTenure::get();
        $resources = [];
        foreach ($landTenures as $landTenure) {
            $resources[] = new LandTenureResource($landTenure);
        }

        return JsonResponseHelper::success($resources, 200);
    }
}
