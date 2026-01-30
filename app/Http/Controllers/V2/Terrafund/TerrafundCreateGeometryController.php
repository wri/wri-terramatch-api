<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\PolygonGeometry;
use App\Services\PolygonService;
use App\Validators\Extensions\Polygons\PlantStartDate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class TerrafundCreateGeometryController extends Controller
{
    public function processGeometry(string $uuid)
    {
        $geometry = PolygonGeometry::isUuid($uuid)->first();

        if ($geometry) {
            return response()->json(['geometry' => $geometry->geo_json], 200);
        } else {
            return response()->json(['error' => 'Geometry not found'], 404);
        }
    }

    public function getCriteriaData(Request $request)
    {
        $uuid = $request->input('uuid');

        $geometry = PolygonGeometry::isUuid($uuid)->first();
        if ($geometry === null) {
            return response()->json(['error' => 'Polygon not found for the given UUID'], 404);
        }

        $criteriaList = GeometryHelper::getCriteriaDataForPolygonGeometry($geometry);

        if (empty($criteriaList)) {
            return response()->json(['error' => 'Criteria data not found for the given polygon ID'], 404);
        }

        return response()->json(['polygon_id' => $uuid, 'criteria_list' => $criteriaList]);
    }

    private function handlePolygonValidation($polygonUuid, $response, $criteriaId): JsonResponse
    {
        if (isset($response['error']) && $response['error'] != null) {
            $status = $response['status'];
            unset($response['valid']);
            unset($response['status']);

            return response()->json($response, $status);
        }
        $extraInfo = $response['extra_info'] ?? null;
        $response['insertion_success'] = App::make(PolygonService::class)
          ->createCriteriaSite($polygonUuid, $criteriaId, $response['valid'], $extraInfo);

        return response()->json($response);
    }

    public function runValidationPolygon(string $uuid)
    {
        try {
            $request = new Request(['uuid' => $uuid]);

            $this->validatePlantStartDate($request);
            App::make(PolygonService::class)->updateSitePolygonValidity($request->input('uuid'));
        } catch (\Exception $e) {
            Log::error('Error during validation polygon: ' . $e->getMessage());

            throw $e;
        }

    }

    public function sendRunValidationPolygon(Request $request)
    {

        $uuid = $request->input('uuid');
        $this->runValidationPolygon($uuid);
        $criteriaData = $this->getCriteriaData($request);

        return $criteriaData;
    }

    public function validatePlantStartDate(Request $request)
    {
        $polygonUuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $polygonUuid,
            PlantStartDate::getValidationData($polygonUuid),
            PolygonService::PLANT_START_DATE_CRITERIA_ID
        );
    }
}
