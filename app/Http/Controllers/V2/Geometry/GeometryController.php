<?php

namespace App\Http\Controllers\V2\Geometry;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Geometry\StoreGeometryRequest;
use App\Models\V2\PointGeometry;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Services\PolygonService;
use App\Validators\SitePolygonValidator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use stdClass;
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
            $results[] = ['polygon_uuids' => $service->createGeojsonModels($geometry, ['source' => PolygonService::GREENHOUSE_SOURCE])];
        }

        // Do the validation in a separate step so that all of the existing polygons are taken into account
        // for things like overlapping and estimated area.
        foreach ($results as $index => $result) {
            $polygonErrors = [];
            foreach ($result['polygon_uuids'] as $polygonUuid) {
                $validationErrors = $this->runStoredGeometryValidations($polygonUuid);
                $estAreaErrors = $this->runStoredGeometryEstAreaValidation($polygonUuid);
                $allErrors = array_merge($validationErrors, $estAreaErrors);
                if (! empty($allErrors)) {
                    $polygonErrors[$polygonUuid] = $allErrors;
                }
            }

            // Send an empty object instead of empty array if there are no errors to keep the response shape consistent.
            data_set($results, "$index.errors", empty($polygonErrors) ? new stdClass() : $polygonErrors);
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
        $projectUuids = [];

        foreach ($polygons as $polygon) {
            $sitePolygon = $polygon->sitePolygon;
            if ($sitePolygon && $sitePolygon->project) {
                $projectUuid = $sitePolygon->project->uuid;
                $projectUuids[] = $projectUuid;
            }
            $polygon->deleteWithRelated();
        }

        $distinctProjectUuids = array_unique($projectUuids);
        $geometryHelper = new GeometryHelper();
        foreach ($distinctProjectUuids as $projectUuid) {
            $geometryHelper->updateProjectCentroid($projectUuid);
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

    protected function runStoredGeometryEstAreaValidation($polygonUuid): array
    {
        $errors = [];
        $sitePolygon = SitePolygon::where('poly_id', $polygonUuid)->first();

        if ($sitePolygon && $sitePolygon->point_id) {
            $pointGeometry = PointGeometry::isUuid($sitePolygon->point_id)->first();

            if ($pointGeometry && isset($pointGeometry->est_area)) {
                if ($pointGeometry->est_area > 5) {
                    $errors[] = [
                        'key' => 'EXCEEDS_EST_AREA',
                        'message' => 'The est_area is bigger than 5',
                        'est_area' => $pointGeometry->est_area,
                    ];
                }
            }
        }

        return $errors;
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
