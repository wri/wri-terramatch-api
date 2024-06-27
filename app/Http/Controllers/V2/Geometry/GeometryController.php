<?php

namespace App\Http\Controllers\V2\Geometry;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Geometry\StoreGeometryRequest;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\Site;
use App\Services\PolygonService;
use App\Validators\SitePolygonValidator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GeometryController extends Controller
{
    public const STORE_GEOMETRY_VALIDATIONS = [
        PolygonService::OVERLAPPING_CRITERIA_ID => 'NOT_OVERLAPPING',
        PolygonService::SELF_CRITERIA_ID => 'SELF_INTERSECTION_UUID',
        PolygonService::COORDINATE_SYSTEM_CRITERIA_ID => 'FEATURE_BOUNDS_UUID',
        PolygonService::SIZE_CRITERIA_ID => 'POLYGON_SIZE_UUID',
        PolygonService::WITHIN_COUNTRY_CRITERIA_ID => 'WITHIN_COUNTRY',
        PolygonService::SPIKE_CRITERIA_ID => 'SPIKES_UUID',
        PolygonService::GEOMETRY_TYPE_CRITERIA_ID => 'GEOMETRY_TYPE_UUID',
        PolygonService::ESTIMATED_AREA_CRITERIA_ID => 'ESTIMATED_AREA',
    ];

    public const NON_PERSISTED_VALIDATIONS = [
        'SELF_INTERSECTION',
        'FEATURE_BOUNDS',
        'POLYGON_SIZE',
        'SPIKES',
        'GEOMETRY_TYPE',
        'SCHEMA',
        'DATA',
    ];

    /**
     * @throws AuthorizationException
     * @deprecated Use POST /api/v2/geometry (include site id in the properties of each feature)
     */
    public function storeSiteGeometry(Request $request, Site $site): JsonResponse
    {
        $this->authorize('uploadPolygons', $site);

        $request->validate([
            'geometries' => 'required|array',
        ]);

        $geometries = $request->input('geometries');
        data_set($geometries, '*.features.*.properties.site_id', $site->uuid);

        return response()->json($this->storeAndValidateGeometries($geometries), 201);
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function storeGeometry(StoreGeometryRequest $request): JsonResponse
    {
        $request->validateGeometries();
        foreach ($request->getSites() as $site) {
            $this->authorize('uploadPolygons', $site);
        }

        return response()->json($this->storeAndValidateGeometries($request->getGeometries()), 201);
    }

    protected function storeAndValidateGeometries($geometries): array
    {
        /** @var PolygonService $service */
        $service = App::make(PolygonService::class);
        $results = [];
        foreach ($geometries as $geometry) {
            $results[] = ['polygon_uuids' => $service->createGeojsonModels($geometry)];
        }

        // Do the validation in a separate step so that all of the existing polygons are taken into account
        // for things like overlapping and estimated area.
        foreach ($results as $index => $result) {
            $polygonErrors = [];
            foreach ($result['polygon_uuids'] as $polygonUuid) {
                $errors = $this->runStoredGeometryValidations($polygonUuid);
                if (! empty($errors)) {
                    $polygonErrors[$polygonUuid] = $errors;
                }
            }

            data_set($results, "$index.errors", $polygonErrors);
        }

        return $results;
    }

    public function validateGeometries(Request $request): JsonResponse
    {
        $request->validate([
            'geometries' => 'required|array',
        ]);

        $geometryErrors = collect();
        foreach ($request->input('geometries') as $geometry) {
            $errors = collect();
            foreach (self::NON_PERSISTED_VALIDATIONS as $validation) {
                try {
                    SitePolygonValidator::validate($validation, $geometry, false);
                } catch (ValidationException $exception) {
                    $errors = $errors->merge(collect($exception->errors())->map(
                        function (array $errorItems, $field) use ($validation) {
                            return collect($errorItems)->map(function ($errorItemString) use ($validation, $field) {
                                $errorItem = json_decode($errorItemString, true);
                                if (array_key_exists('key', $errorItem)) {
                                    // This is an error that came from one of our geometry validations
                                    $errorItem['field'] = $field;

                                    return $errorItem;
                                } else {
                                    // This is an error that came from the schema or data validations. The last item in the
                                    // array contains a descriptive message, so we can simply return that one.
                                    return [
                                        'field' => $field,
                                        'key' => $validation,
                                        'message' => array_pop($errorItem),
                                    ];
                                }
                            });
                        }
                    ));
                }
            }
            $geometryErrors->push($errors->values()->flatten(1));
        }

        if (collect($geometryErrors)->flatten()->isEmpty()) {
            return response()->json(['errors' => []]);
        } else {
            return response()->json(['errors' => $geometryErrors], 422);
        }
    }

    public function deleteGeometries(Request $request): JsonResponse
    {
        $uuids = $request->input('uuids');
        if (empty($uuids)) {
            throw new NotFoundHttpException();
        }

        $polygons = PolygonGeometry::whereIn('uuid', $uuids)->get();
        if (count($polygons) != count($uuids)) {
            throw new NotFoundHttpException();
        }

        foreach ($polygons as $polygon) {
            $this->authorize('delete', $polygon);
        }

        foreach ($polygons as $polygon) {
            $polygon->deleteWithRelated();
            $polygon->delete();
        }

        return response()->json(['success' => 'geometries have been deleted'], 202);
    }

    public function updateGeometry(Request $request, PolygonGeometry $polygon): JsonResponse
    {
        $this->authorize('update', $polygon);

        $geometry = $request->input('geometry');
        /** @var PolygonService $service */
        $service = App::make(PolygonService::class);
        $service->updateGeojsonModels($polygon, $geometry);

        $errors = $this->runStoredGeometryValidations($polygon->uuid);

        return response()->json(['errors' => $errors], 200);
    }

    protected function runStoredGeometryValidations(string $polygonUuid): array
    {
        // TODO: remove when the point transformation ticket is complete
        if ($polygonUuid == PolygonService::TEMP_FAKE_POLYGON_UUID) {
            return [];
        }

        /** @var PolygonService $service */
        $service = App::make(PolygonService::class);
        $data = ['geometry' => $polygonUuid];
        $errors = [];
        foreach (self::STORE_GEOMETRY_VALIDATIONS as $criteriaId => $validation) {
            $valid = true;

            try {
                SitePolygonValidator::validate($validation, $data);
            } catch (ValidationException $exception) {
                $valid = false;
                $errors[] = json_decode($exception->errors()['geometry'][0]);
            }

            $service->createCriteriaSite($polygonUuid, $criteriaId, $valid);
        }

        // For these two, the polygon service already handled creating the site criteria, so we just need to
        // report on them if not valid
        $polygon = PolygonGeometry::isUuid($polygonUuid)->select('uuid')->first();
        $schemaCriteria = $polygon->criteriaSite()->forCriteria(PolygonService::SCHEMA_CRITERIA_ID)->first();
        if ($schemaCriteria != null && ! $schemaCriteria->valid) {
            $errors[] = [
                'key' => 'TABLE_SCHEMA',
                'message' => 'The properties for the geometry are missing some required values.',
            ];
        } else {
            // only report data validation if the schema was valid. When the schema is invalid, the data is
            // always invalid as well.
            $dataCriteria = $polygon->criteriaSite()->forCriteria(PolygonService::DATA_CRITERIA_ID)->first();
            if ($dataCriteria != null && ! $dataCriteria->valid) {
                $errors[] = [
                    'key' => 'DATA_COMPLETED',
                    'message' => 'The properties for the geometry have some invalid values.',
                ];
            }
        }

        return $errors;
    }
}
