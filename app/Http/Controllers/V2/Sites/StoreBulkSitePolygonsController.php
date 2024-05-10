<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Services\PolygonService;
use App\Validators\SitePolygonValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

class StoreBulkSitePolygonsController extends Controller
{
    const VALIDATIONS = [
        PolygonService::OVERLAPPING_CRITERIA_ID => 'NOT_OVERLAPPING',
        PolygonService::SELF_CRITERIA_ID => 'SELF_INTERSECTION_UUID',
        PolygonService::SIZE_CRITERIA_ID => 'POLYGON_SIZE_UUID',
        PolygonService::WITHIN_COUNTRY_CRITERIA_ID => 'WITHIN_COUNTRY',
        PolygonService::SPIKE_CRITERIA_ID => 'SPIKES_UUID',
        // TODO
//        PolygonService::GEOMETRY_TYPE_CRITERIA_ID =>
        PolygonService::ESTIMATED_AREA_CRITERIA_ID => 'ESTIMATED_AREA',
    ];

    public function __invoke(Request $request, Site $site): JsonResponse
    {
        $this->authorize('uploadPolygons', $site);

        $request->validate([
            'geometries' => 'required|array',
        ]);

        /** @var PolygonService $service */
        $service = App::make(PolygonService::class);
        $polygonUuids = [];
        foreach ($request->input('geometries') as $geometry) {
            // We expect single polys on this endpoint, so just pull the first uuid returned
            $polygonUuids[] = $service->createGeojsonModels($geometry, ['site_id' => $site->uuid])[0];
        }

        // Do the validation in a separate step so that all of the existing polygons are taken into account
        // for things like overlapping and estimated area.
        foreach ($polygonUuids as $polygonUuid) {
            $data = ['polygon_uuid' => $polygonUuid];
            foreach (self::VALIDATIONS as $criteriaId => $validation) {
                $valid = true;
                try {
                    SitePolygonValidator::validate($validation, $data);
                } catch (ValidationException $exception) {
                    $valid = false;
                }

                $service->createCriteriaSite($polygonUuid, $criteriaId, $valid);
                // TODO: accumulate errors and add to response
            }
        }

        return response()->json(['polygon_uuids' => $polygonUuids]);
    }
}
