<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\Site;
use App\Services\PolygonService;
use App\Validators\SitePolygonValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StoreBulkSiteGeometryController extends Controller
{
    public const VALIDATIONS = [
        PolygonService::OVERLAPPING_CRITERIA_ID => 'NOT_OVERLAPPING',
        PolygonService::SELF_CRITERIA_ID => 'SELF_INTERSECTION_UUID',
        PolygonService::COORDINATE_SYSTEM_CRITERIA_ID => 'FEATURE_BOUNDS_UUID',
        PolygonService::SIZE_CRITERIA_ID => 'POLYGON_SIZE_UUID',
        PolygonService::WITHIN_COUNTRY_CRITERIA_ID => 'WITHIN_COUNTRY',
        PolygonService::SPIKE_CRITERIA_ID => 'SPIKES_UUID',
        PolygonService::GEOMETRY_TYPE_CRITERIA_ID => 'GEOMETRY_TYPE_UUID',
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
        $polygonErrors = [];
        foreach ($polygonUuids as $polygonUuid) {
            $data = ['geometry' => $polygonUuid];
            foreach (self::VALIDATIONS as $criteriaId => $validation) {
                $valid = true;

                try {
                    SitePolygonValidator::validate($validation, $data);
                } catch (ValidationException $exception) {
                    $valid = false;
                    Log::info('ValidationException: ' . $validation . ', ' . $exception->getMessage());
                    $polygonErrors[$polygonUuid][] = json_decode($exception->errors()['geometry'][0]);
                }

                $service->createCriteriaSite($polygonUuid, $criteriaId, $valid);
            }

            // For these two, the createGeojsonModels already handled creating the site criteria, so we just need to
            // report on them if not valid
            $polygon = PolygonGeometry::isUuid($polygonUuid)->select('uuid')->first();
            $schemaCriteria = $polygon->criteriaSite()->forCriteria(PolygonService::SCHEMA_CRITERIA_ID)->first();
            if ($schemaCriteria != null && ! $schemaCriteria->valid) {
                $polygonErrors[$polygonUuid][] = [
                    'key' => 'TABLE_SCHEMA',
                    'message' => 'The properties for the geometry are missing some required values.',
                ];
            } else {
                // only report data validation if the schema was valid. When the schema is invalid, the data is
                // always invalid as well.
                $dataCriteria = $polygon->criteriaSite()->forCriteria(PolygonService::DATA_CRITERIA_ID)->first();
                if ($dataCriteria != null && ! $dataCriteria->valid) {
                    $polygonErrors[$polygonUuid][] = [
                        'key' => 'DATA_COMPLETED',
                        'message' => 'The properties for the geometry have some invalid values.',
                    ];
                }
            }
        }

        return response()->json(['polygon_uuids' => $polygonUuids, 'errors' => $polygonErrors], 201);
    }
}
