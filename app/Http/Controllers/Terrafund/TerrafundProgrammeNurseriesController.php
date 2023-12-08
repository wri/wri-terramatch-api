<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Terrafund\TerrafundProgramme;
use App\Resources\Terrafund\TerrafundNurseryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerrafundProgrammeNurseriesController extends Controller
{
    public function readAllProgrammeNurseries(Request $request, TerrafundProgramme $terrafundProgramme): JsonResponse
    {
        $this->authorize('read', $terrafundProgramme);

        $nurseries = $terrafundProgramme->terrafundNurseries()->paginate(5);
        $resources = [];
        foreach ($nurseries as $nursery) {
            $resources[] = new TerrafundNurseryResource($nursery);
        }

        $meta = (object)[
            'first' => $nurseries->firstItem(),
            'current' => $nurseries->currentPage(),
            'last' => $nurseries->lastPage(),
            'total' => $nurseries->total(),
        ];

        return JsonResponseHelper::success($resources, 200, $meta);
    }

    public function checkHasProgrammeNurseries(Request $request, TerrafundProgramme $terrafundProgramme): JsonResponse
    {
        $this->authorize('read', $terrafundProgramme);

        $nurseriesCount = $terrafundProgramme->terrafundNurseries()->count();

        $response = [
            'programme_id' => $terrafundProgramme->id,
            'has_nurseries' => $nurseriesCount > 0,
        ];

        return JsonResponseHelper::success($response, 200);
    }
}
