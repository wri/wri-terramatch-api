<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\InsertGeojsonToDBJob;
use App\Jobs\RunSitePolygonsValidationJob;
use App\Jobs\SiteValidationJob;
use App\Models\DelayedJob;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\WorldCountryGeneralized;
use App\Services\PolygonService;
use App\Services\SiteService;
use App\Validators\Extensions\Polygons\EstimatedArea;
use App\Validators\Extensions\Polygons\FeatureBounds;
use App\Validators\Extensions\Polygons\GeometryType;
use App\Validators\Extensions\Polygons\NotOverlapping;
use App\Validators\Extensions\Polygons\PolygonSize;
use App\Validators\Extensions\Polygons\SelfIntersection;
use App\Validators\Extensions\Polygons\Spikes;
use App\Validators\Extensions\Polygons\WithinCountry;
use App\Validators\SitePolygonValidator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class TerrafundCreateGeometryController extends Controller
{
    private const MAX_EXECUTION_TIME = 240;

    public function processGeometry(string $uuid)
    {
        $geometry = PolygonGeometry::isUuid($uuid)->first();

        if ($geometry) {
            return response()->json(['geometry' => $geometry->geo_json], 200);
        } else {
            return response()->json(['error' => 'Geometry not found'], 404);
        }
    }

    public function storeGeometry(Request $request)
    {
        $request->validate([
          'geometry' => 'required|json',
        ]);

        $geometry = json_decode($request->input('geometry'));
        $geom = DB::raw("ST_GeomFromGeoJSON('" . json_encode($geometry) . "')");

        $polygonGeometry = PolygonGeometry::create([
          'geom' => $geom,
          'created_by' => Auth::user()?->id,
        ]);

        return response()->json(['uuid' => $polygonGeometry->uuid], 200);
    }

    public function validateDataInDB(Request $request)
    {
        $polygonUuid = $request->input('uuid');
        $fieldsToValidate = ['poly_name', 'plantstart', 'plantend', 'practice', 'target_sys', 'distr', 'num_trees'];
        // Check if the polygon with the specified poly_id exists
        $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
        if (! $sitePolygon) {
            return response()->json(['valid' => false, 'message' => 'No site polygon found with the specified UUID.']);
        }

        // Proceed with validation of attribute values
        $validationErrors = [];
        $polygonService = App::make(PolygonService::class);
        foreach ($fieldsToValidate as $field) {
            $value = $sitePolygon->$field;
            if ($polygonService->isInvalidField($field, $value)) {
                $validationErrors[] = [
                    'field' => $field,
                    'error' => $value,
                    'exists' => ! is_null($value) && $value !== '',
                ];
            }
        }

        $isValid = empty($validationErrors);
        $responseData = ['valid' => $isValid];
        if (! $isValid) {
            $responseData['message'] = 'Some attributes of the site polygon are invalid.';
        }

        $polygonService->createCriteriaSite($polygonUuid, PolygonService::DATA_CRITERIA_ID, $isValid, $validationErrors);

        return response()->json($responseData);
    }

    public function getGeometryProperties(string $geojsonFilename)
    {
        $tempDir = sys_get_temp_dir();
        $geojsonPath = $tempDir . DIRECTORY_SEPARATOR . $geojsonFilename;
        $geojsonData = file_get_contents($geojsonPath);
        $geojson = json_decode($geojsonData, true);
        if (! isset($geojson['features'])) {
            return ['error' => 'GeoJSON file does not contain features'];
        }

        $propertiesList = [];
        foreach ($geojson['features'] as $feature) {
            $properties = $feature['properties'];
            $geometryType = $feature['geometry']['type'];

            if ($geometryType === 'Polygon' || $geometryType === 'MultiPolygon') {
                $propertiesList[] = $properties;
            }
        }

        return $propertiesList;
    }

    public function uploadKMLFileProject(Request $request)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');

        $rules = [
          'entity_uuid' => 'required|string',
          'entity_type' => 'required|string',
          'file' => [
              'required',
              'file',
              function ($attribute, $value, $fail) {
                  if (! $value->getClientOriginalName() || ! preg_match('/\.kml$/i', $value->getClientOriginalName())) {
                      $fail('The file must have a .kml extension.');
                  }
              },
          ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $this->parseValidationErrors($validator->errors());

            return response()->json(['errors' => $errors], 422);
        }
        $entity_uuid = $request->get('entity_uuid');
        $entity_type = $request->get('entity_type');
        $kmlfile = $request->file('file');
        $tempDir = sys_get_temp_dir();
        $filename = uniqid('kml_file_') . '.' . $kmlfile->getClientOriginalExtension();
        $kmlPath = $tempDir . DIRECTORY_SEPARATOR . $filename;
        $kmlfile->move($tempDir, $filename);
        $geojsonFilename = Str::replaceLast('.kml', '.geojson', $filename);
        $geojsonPath = $tempDir . DIRECTORY_SEPARATOR . $geojsonFilename;
        $process = new Process(['ogr2ogr', '-f', 'GeoJSON', $geojsonPath, $kmlPath]);
        $process->run();
        if (! $process->isSuccessful()) {
            Log::error('Error converting KML to GeoJSON: ' . $process->getErrorOutput());

            return response()->json(['error' => 'Failed to convert KML to GeoJSON', 'message' => $process->getErrorOutput()], 500);
        }

        $uuid = App::make(PolygonService::class)->insertGeojsonToDB($geojsonFilename, $entity_uuid, $entity_type);
        if (isset($uuid['error'])) {
            return response()->json(['error' => 'Geometry not inserted into DB', 'message' => $uuid['error']], 500);
        }

        App::make(SiteService::class)->setSiteToRestorationInProgress($entity_uuid);

        return response()->json(['message' => 'KML file processed and inserted successfully', 'uuid' => $uuid], 200);

    }

    public function uploadKMLFile(Request $request)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');

        $rules = [
          'file' => [
              'required',
              'file',
              function ($attribute, $value, $fail) {
                  if (! $value->getClientOriginalName() || ! preg_match('/\.kml$/i', $value->getClientOriginalName())) {
                      $fail('The file must have a .kml extension.');
                  }
              },
          ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $this->parseValidationErrors($validator->errors());

            return response()->json(['errors' => $errors], 422);
        }

        $body = $request->all();
        $site_id = $request->input('uuid');
        $kmlfile = $request->file('file');
        $tempDir = sys_get_temp_dir();
        $filename = uniqid('kml_file_') . '.' . $kmlfile->getClientOriginalExtension();
        $kmlPath = $tempDir . DIRECTORY_SEPARATOR . $filename;
        $kmlfile->move($tempDir, $filename);
        $geojsonFilename = Str::replaceLast('.kml', '.geojson', $filename);
        $geojsonPath = $tempDir . DIRECTORY_SEPARATOR . $geojsonFilename;
        $process = new Process(['ogr2ogr', '-f', 'GeoJSON', $geojsonPath, $kmlPath]);
        $process->run();
        if (! $process->isSuccessful()) {
            Log::error('Error converting KML to GeoJSON: ' . $process->getErrorOutput());

            return response()->json(['error' => 'Failed to convert KML to GeoJSON', 'message' => $process->getErrorOutput()], 500);
        }
        $geojsonContent = file_get_contents($geojsonPath);
        $polygonLoadedList = isset($body['polygon_loaded']) &&
            filter_var($body['polygon_loaded'], FILTER_VALIDATE_BOOLEAN);
        $submitPolygonsLoaded = isset($body['submit_polygon_loaded']) &&
            filter_var($body['submit_polygon_loaded'], FILTER_VALIDATE_BOOLEAN);

        if ($polygonLoadedList) {
            $polygonLoaded = $this->GetAllPolygonsLoaded($geojsonContent, $site_id);

            return response()->json($polygonLoaded->original, 200);
        }

        $redis_key = 'kml_file_' . uniqid();
        Redis::set($redis_key, $geojsonContent, 'EX', 7200);
        $delayedJob = DelayedJob::create();

        $job = new InsertGeojsonToDBJob(
            $redis_key,
            $delayedJob->id,
            $site_id,
            'site',
            $body['primary_uuid'] ?? null,
            $submitPolygonsLoaded
        );

        dispatch($job);

        return (new DelayedJobResource($delayedJob))
            ->additional(['message' => 'KML queued to insert']);


    }

    private function findShpFile($directory)
    {
        Log::info('find shp: ' . $directory);

        $shpFile = null;
        $files = scandir($directory);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'shp') {
                $shpFile = "{$directory}/{$file}";

                break;
            }
        }

        return $shpFile;
    }

    public function parseValidationErrors($errors)
    {
        $parsedErrors = [];
        foreach ($errors->messages() as $field => $messages) {
            $parsedErrors[$field] = array_map(function ($message) {
                $decoded = json_decode($message, true);

                return $decoded[3] ?? $message; // Return the last element if it's JSON, otherwise return the original message
            }, $messages);
        }

        return $parsedErrors;
    }

    public function uploadShapefileProject(Request $request)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');
        Log::debug('Upload Shape file data', ['request' => $request->all()]);
        $rules = [
          'entity_uuid' => 'required|string',
          'entity_type' => 'required|string',
          'file' => 'required|file|mimes:zip',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $this->parseValidationErrors($validator->errors());

            return response()->json(['errors' => $errors], 422);
        }
        $entity_uuid = $request->get('entity_uuid');
        $entity_type = $request->get('entity_type');
        $file = $request->file('file');
        $tempDir = sys_get_temp_dir();
        $directory = $tempDir . DIRECTORY_SEPARATOR . uniqid('shapefile_');
        mkdir($directory, 0755, true);
        // Extract the contents of the ZIP file
        $zip = new \ZipArchive();
        if ($zip->open($file->getPathname()) === true) {
            $zip->extractTo($directory);
            $zip->close();
            $shpFile = $this->findShpFile($directory);
            if (! $shpFile) {
                return response()->json(['error' => 'Shapefile (.shp) not found in the ZIP file'], 400);
            }
            $geojsonFilename = Str::replaceLast('.shp', '.geojson', basename($shpFile));
            $geojsonPath = $tempDir . DIRECTORY_SEPARATOR . $geojsonFilename;
            $process = new Process(['ogr2ogr', '-f', 'GeoJSON', $geojsonPath, $shpFile]);
            $process->run();
            if (! $process->isSuccessful()) {
                Log::error('Error converting Shapefile to GeoJSON: ' . $process->getErrorOutput());

                return response()->json(['error' => 'Failed to convert Shapefile to GeoJSON', 'message' => $process->getErrorOutput()], 500);
            }
            $uuid = App::make(PolygonService::class)->insertGeojsonToDB($geojsonFilename, $entity_uuid, $entity_type);
            if (isset($uuid['error'])) {
                return response()->json(['error' => 'Geometry not inserted into DB', 'message' => $uuid['error']], 500);
            }

            return response()->json(['message' => 'Shape file processed and inserted successfully', 'uuid' => $uuid], 200);
        } else {
            return response()->json(['error' => 'Failed to open the ZIP file'], 400);
        }
    }

    public function uploadShapefile(Request $request)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');
        $rules = [
          'file' => 'required|file|mimes:zip',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $this->parseValidationErrors($validator->errors());

            return response()->json(['errors' => $errors], 422);
        }

        try {
            $body = $request->all();
            $site_id = $request->input('uuid');
            $file = $request->file('file');
            if ($file->getClientOriginalExtension() !== 'zip') {
                return response()->json(['error' => 'Only ZIP files are allowed'], 400);
            }
            $tempDir = sys_get_temp_dir();
            $directory = $tempDir . DIRECTORY_SEPARATOR . uniqid('shapefile_');
            mkdir($directory, 0755, true);
            $zip = new \ZipArchive();
            if ($zip->open($file->getPathname()) === true) {
                $zip->extractTo($directory);
                $zip->close();
                $shpFile = $this->findShpFile($directory);
                if (! $shpFile) {
                    return response()->json(['error' => 'Shapefile (.shp) not found in the ZIP file'], 400);
                }
                $geojsonFilename = Str::replaceLast('.shp', '.geojson', basename($shpFile));
                $geojsonPath = $tempDir . DIRECTORY_SEPARATOR . $geojsonFilename;
                $process = new Process(['ogr2ogr', '-f', 'GeoJSON', $geojsonPath, $shpFile]);
                $process->run();
                if (! $process->isSuccessful()) {
                    Log::error('Error converting Shapefile to GeoJSON: ' . $process->getErrorOutput());

                    return response()->json(['error' => 'Failed to convert Shapefile to GeoJSON', 'message' => $process->getErrorOutput()], 500);
                }
                $geojsonContent = file_get_contents($geojsonPath);
                $polygonLoadedList = isset($body['polygon_loaded']) &&
                  filter_var($body['polygon_loaded'], FILTER_VALIDATE_BOOLEAN);
                $submitPolygonsLoaded = isset($body['submit_polygon_loaded']) &&
                  filter_var($body['submit_polygon_loaded'], FILTER_VALIDATE_BOOLEAN);

                if ($polygonLoadedList) {
                    $polygonLoaded = $this->GetAllPolygonsLoaded($geojsonContent, $site_id);

                    return response()->json($polygonLoaded->original, 200);
                }

                $redis_key = 'shapefile_file_' . uniqid();
                Redis::set($redis_key, $geojsonContent, 'EX', 7200);
                $delayedJob = DelayedJob::create();

                $job = new InsertGeojsonToDBJob(
                    $redis_key,
                    $delayedJob->id,
                    $site_id,
                    'site',
                    $body['primary_uuid'] ?? null,
                    $submitPolygonsLoaded
                );

                dispatch($job);

                return (new DelayedJobResource($delayedJob))
                    ->additional(['message' => 'Shapefile queued to insert']);


            } else {
                return response()->json(['error' => 'Failed to open the ZIP file'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function checkSelfIntersection(Request $request)
    {
        $uuid = $request->query('uuid');
        $geometry = PolygonGeometry::where('uuid', $uuid)->first();

        if (! $geometry) {
            return response()->json(['error' => 'Geometry not found'], 404);
        }

        $isSimple = SelfIntersection::uuidValid($uuid);
        $message = $isSimple ? 'The geometry is valid' : 'The geometry has self-intersections';
        $insertionSuccess = App::make(PolygonService::class)
          ->createCriteriaSite($uuid, PolygonService::SELF_CRITERIA_ID, $isSimple);

        return response()->json(['selfintersects' => $message, 'geometry_id' => $geometry->id, 'insertion_success' => $insertionSuccess, 'valid' => $isSimple ? true : false], 200);
    }

    public function checkBoundarySegments(Request $request)
    {
        $uuid = $request->query('uuid');
        $geometry = PolygonGeometry::isUuid($uuid)->first();

        if (! $geometry) {
            return response()->json(['error' => 'Geometry not found'], 404);
        }
        $spikes = Spikes::detectSpikes($geometry->geo_json);
        $valid = count($spikes) === 0;
        $insertionSuccess = App::make(PolygonService::class)
          ->createCriteriaSite($uuid, PolygonService::SPIKE_CRITERIA_ID, $valid);

        return response()->json(['spikes' => $spikes, 'geometry_id' => $uuid, 'insertion_success' => $insertionSuccess, 'valid' => $valid], 200);
    }

    public function validatePolygonSize(Request $request)
    {
        $uuid = $request->query('uuid');
        $geometry = PolygonGeometry::isUuid($uuid)->first();

        if (! $geometry) {
            return response()->json(['error' => 'Geometry not found'], 404);
        }

        $areaSqMeters = PolygonSize::calculateSqMeters($geometry->db_geometry);
        $valid = $areaSqMeters <= PolygonSize::SIZE_LIMIT;
        $insertionSuccess = App::make(PolygonService::class)
          ->createCriteriaSite($uuid, PolygonService::SIZE_CRITERIA_ID, $valid);

        return response()->json([
          'area_hectares' => $areaSqMeters / 10000, // Convert to hectares
          'area_sqmeters' => $areaSqMeters,
          'geometry_id' => $geometry->id,
          'insertion_success' => $insertionSuccess,
          'valid' => $valid,
        ], 200);
    }

    public function checkWithinCountry(Request $request)
    {
        $polygonUuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $polygonUuid,
            WithinCountry::getIntersectionData($polygonUuid),
            PolygonService::WITHIN_COUNTRY_CRITERIA_ID
        );
    }

    public function getGeometryType(Request $request)
    {
        $uuid = $request->input('uuid');

        $geometryType = PolygonGeometry::getGeometryType($uuid);
        if ($geometryType) {
            $valid = $geometryType === GeometryType::VALID_TYPE_POLYGON || $geometryType === GeometryType::VALID_TYPE_MULTIPOLYGON;
            $insertionSuccess = App::make(PolygonService::class)
              ->createCriteriaSite($uuid, PolygonService::GEOMETRY_TYPE_CRITERIA_ID, $valid);

            return response()->json(['uuid' => $uuid, 'geometry_type' => $geometryType, 'valid' => $valid, 'insertion_success' => $insertionSuccess]);
        } else {
            return response()->json(['error' => 'Geometry not found for the given UUID'], 404);
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

    public function getCriteriaDataForMultiplePolygons(array $uuids)
    {
        $result = [];
        $unprocessed = [];

        foreach ($uuids as $uuid) {
            $geometry = PolygonGeometry::isUuid($uuid)->first();

            if ($geometry === null) {
                continue;
            }
            $criteriaList = GeometryHelper::getCriteriaDataForPolygonGeometry($geometry);

            if (empty($criteriaList)) {
                $unprocessed[] = ['uuid' => $uuid, 'error' => 'Criteria data not found for the given polygon'];

                continue;
            }
            $result[] = ['polygon_id' => $uuid, 'criteria_list' => $criteriaList];
        }

        return response()->json($result);
    }

    public function uploadGeoJSONFileProject(Request $request)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');

        $rules = [
          'entity_uuid' => 'required|string',
          'entity_type' => 'required|string',
          'file' => 'required|file|mimes:json,geojson',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $this->parseValidationErrors($validator->errors());

            return response()->json(['errors' => $errors], 422);
        }
        $entity_uuid = $request->get('entity_uuid');
        $entity_type = $request->get('entity_type');
        $file = $request->file('file');
        $tempDir = sys_get_temp_dir();
        $filename = uniqid('geojson_file_') . '.' . $file->getClientOriginalExtension();
        $file->move($tempDir, $filename);
        $uuid = App::make(PolygonService::class)->insertGeojsonToDB($filename, $entity_uuid, $entity_type);
        if (is_array($uuid) && isset($uuid['error'])) {
            return response()->json(['error' => 'Failed to insert GeoJSON data into the database', 'message' => $uuid['error']], 500);
        }

        return response()->json(['message' => 'Geojson file processed and inserted successfully', 'uuid' => $uuid], 200);

    }

    public function uploadGeoJSONFile(Request $request)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');
        $rules = [
          'file' => 'required|file|mimes:json,geojson',
        ];
        $body = $request->all();
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $this->parseValidationErrors($validator->errors());

            return response()->json(['errors' => $errors], 422);
        }
        $site_id = $request->input('uuid');
        $file = $request->file('file');
        $tempDir = sys_get_temp_dir();
        $filename = uniqid('geojson_file_') . '.' . $file->getClientOriginalExtension();
        $file->move($tempDir, $filename);
        $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;
        $geojson_content = file_get_contents($filePath);
        $polygonLoadedList = isset($body['polygon_loaded']) &&
            filter_var($body['polygon_loaded'], FILTER_VALIDATE_BOOLEAN);
        $submitPolygonsLoaded = isset($body['submit_polygon_loaded']) &&
            filter_var($body['submit_polygon_loaded'], FILTER_VALIDATE_BOOLEAN);

        if ($polygonLoadedList) {
            $polygonLoaded = $this->GetAllPolygonsLoaded($geojson_content, $site_id);

            return response()->json($polygonLoaded->original, 200);
        }


        $redis_key = 'geojson_file_' . uniqid();
        Redis::set($redis_key, $geojson_content, 'EX', 7200);
        $delayedJob = DelayedJob::create();

        $job = new InsertGeojsonToDBJob(
            $redis_key,
            $delayedJob->id,
            $site_id,
            'site',
            $body['primary_uuid'] ?? null,
            $submitPolygonsLoaded
        );

        dispatch($job);

        return (new DelayedJobResource($delayedJob))
            ->additional(['message' => 'Geojson queued to insert']);


    }

    public function validateGeojson($filePath)
    {
        $csvData = [];
        $geojsonData = file_get_contents($filePath);
        $geojson = json_decode($geojsonData, true);
        $splittedgeojson = GeometryHelper::splitMultiPolygons($geojson);
        $groupedByProject = GeometryHelper::groupFeaturesByProjectAndSite($splittedgeojson);
        $isValidArea = false;

        try {
            foreach ($groupedByProject as $projectUuid => $sites) {
                if ($projectUuid !== 'no_project') {
                    $currentAreaValuesForProject = EstimatedArea::getAreaOfProject($projectUuid);
                    $newTotalArea = 0;
                    $siteAreas = [];
                    foreach ($sites as $featureCollection) {
                        $siteId = $featureCollection['features'][0]['properties']['site_id'];
                        if ($siteId !== 'no_site') {
                            $siteAreas[$siteId] = 0;
                            foreach ($featureCollection['features'] as $feature) {
                                $siteAreas[$siteId] += PolygonSize::getArea($feature['geometry']);
                            }
                            $newTotalArea += $siteAreas[$siteId];
                        }
                    }


                    $sumArea = $currentAreaValuesForProject['sum_area_project'] + $newTotalArea;
                    $isValidArea = $sumArea >= $currentAreaValuesForProject['lower_bound'] && $sumArea <= $currentAreaValuesForProject['upper_bound'];
                }
                foreach ($sites as $featureCollection) {
                    $selfIntersections = NotOverlapping::checkFeatureIntersections($featureCollection['features']);

                    foreach ($featureCollection['features'] as $index => $feature) {
                        $siteId = $feature['properties']['site_id'] ?? null;
                        if ($siteId && $feature['geometry']['type'] === 'Polygon' && Str::isUuid($siteId)) {
                            $geojsonInside = json_encode($feature['geometry']);
                            $validationGeojson = ['features' => ['feature' => ['properties' => $feature['properties']]]];

                            $validOverlappingDB = in_array($index, $selfIntersections['intersections']) ?
                              ['valid' => false] : NotOverlapping::doesNotOverlap($geojsonInside, $feature['properties']['site_id']);

                            $validations = [
                              'nonOverlapping' => $validOverlappingDB['valid'] ?? false,
                              'nonSelfIntersection' => SelfIntersection::geoJsonValid($feature['geometry']),
                              'insideCoordinateSystem' => FeatureBounds::geoJsonValid($feature),
                              'nonSurpassSizeLimit' => PolygonSize::geoJsonValid($feature['geometry']),
                              'insideCountry' => WithinCountry::getIntersectionDataWithSiteId($geojsonInside, $feature['properties']['site_id'])['valid'] ?? false,
                              'noSpikes' => Spikes::geoJsonValid($feature['geometry']),
                              'validPolygonType' => GeometryType::geoJsonValid($feature['geometry']),
                              'nonSurpassEstimatedArea' => $isValidArea,
                              'completeData' => SitePolygonValidator::isValid('SCHEMA', $validationGeojson) && SitePolygonValidator::isValid('DATA', $validationGeojson),
                            ];

                            $approvalValidations = array_filter($validations, function ($key) {
                                return ! in_array($key, ['nonSurpassEstimatedArea', 'completeData']);
                            }, ARRAY_FILTER_USE_KEY);

                            $canBeApproved = array_reduce($approvalValidations, fn ($carry, $item) => $carry && $item, true);

                            $csvData[] = $this->makeCSVRow($feature, $validations, $canBeApproved, false);
                        } else {
                            $csvData[] = $this->makeCSVRow($feature, [], false, true);
                        }
                    }
                }
            }
        } catch(Exception $e) {
            Log::info('Error: '.$e->getMessage());
        }


        return $csvData;
    }

    public function makeCSVRow($feature, $validations, $canBeApproved, $isEmpty)
    {
        if ($isEmpty) {
            return [
              'polygon_name' => $feature['properties']['poly_name'] ?? 'Unnamed Polygon',
              'site_uuid' => $feature['properties']['site_id'] ?? '',
              'No Overlapping' => '',
              'No Self-intersection' => '',
              'Inside Coordinate System' => '',
              'Inside Size Limit' => '',
              'Within Country' => '',
              'No Spikes' => '',
              'Polygon Type' => '',
              'Within Total Area Expected' => '',
              'Completed Data' => '',
              'Can Be Approved?' => 'No Site ID Available',
            ];
        } else {
            return [
              'polygon_name' => $feature['properties']['poly_name'] ?? 'Unnamed Polygon',
              'site_uuid' => $feature['properties']['site_id'],
              'No Overlapping' => $validations['nonOverlapping'] ? 'TRUE' : 'FALSE',
              'No Self-intersection' => $validations['nonSelfIntersection'] ? 'TRUE' : 'FALSE',
              'Inside Coordinate System' => $validations['insideCoordinateSystem'] ? 'TRUE' : 'FALSE',
              'Inside Size Limit' => $validations['nonSurpassSizeLimit'] ? 'TRUE' : 'FALSE',
              'Within Country' => $validations['insideCountry'] ? 'TRUE' : 'FALSE',
              'No Spikes' => $validations['noSpikes'] ? 'TRUE' : 'FALSE',
              'Polygon Type' => $validations['validPolygonType'] ? 'TRUE' : 'FALSE',
              'Within Total Area Expected' => $validations['nonSurpassEstimatedArea'] ? 'TRUE' : 'FALSE',
              'Completed Data' => $validations['completeData'] ? 'TRUE' : 'FALSE',
              'Can Be Approved?' => $canBeApproved ? 'YES' : 'NO',
            ];
        }
    }

    public function uploadGeoJSONFileWithValidation(Request $request)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');
        $rules = [
          'file' => 'required|file|mimes:json,geojson',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $this->parseValidationErrors($validator->errors());

            return response()->json(['errors' => $errors], 422);
        }

        $file = $request->file('file');
        $tempDir = sys_get_temp_dir();
        $filename = uniqid('geojson_file_') . '.' . $file->getClientOriginalExtension();
        $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;
        $file->move($tempDir, $filename);


        $csvData = $this->validateGeojson($filePath);


        $csvContent = array_merge([implode(',', array_keys($csvData[0]))], array_map(fn ($row) => implode(',', $row), $csvData));
        $csvContent = implode("\n", $csvContent);

        $response = Response::make($csvContent, 200, [
          'Content-Type' => 'text/csv',
          'Content-Disposition' => 'attachment; filename="validation_results_' . date('Y-m-d_H-i-s') . '.csv"',
        ]);

        unlink($filePath);

        return $response;
    }

    public function uploadShapefileWithValidation(Request $request)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');
        $rules = [
          'file' => 'required|file|mimes:zip',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $this->parseValidationErrors($validator->errors());

            return response()->json(['errors' => $errors], 422);
        }
        $file = $request->file('file');
        if ($file->getClientOriginalExtension() !== 'zip') {
            return response()->json(['error' => 'Only ZIP files are allowed'], 400);
        }
        $tempDir = sys_get_temp_dir();
        $directory = $tempDir . DIRECTORY_SEPARATOR . uniqid('shapefile_');
        mkdir($directory, 0755, true);

        // Extract the contents of the ZIP file
        $zip = new \ZipArchive();
        if ($zip->open($file->getPathname()) === true) {
            $zip->extractTo($directory);
            $zip->close();
            $shpFile = $this->findShpFile($directory);
            if (! $shpFile) {
                return response()->json(['error' => 'Shapefile (.shp) not found in the ZIP file'], 400);
            }
            $geojsonFilename = Str::replaceLast('.shp', '.geojson', basename($shpFile));
            $geojsonPath = $tempDir . DIRECTORY_SEPARATOR . $geojsonFilename;
            $process = new Process(['ogr2ogr', '-f', 'GeoJSON', $geojsonPath, $shpFile]);
            $process->run();
            if (! $process->isSuccessful()) {
                Log::error('Error converting Shapefile to GeoJSON: ' . $process->getErrorOutput());

                return response()->json(['error' => 'Failed to convert Shapefile to GeoJSON', 'message' => $process->getErrorOutput()], 500);
            }

            $csvData = $this->validateGeojson($geojsonPath);
            $csvContent = array_merge([implode(',', array_keys($csvData[0]))], array_map(fn ($row) => implode(',', $row), $csvData));
            $csvContent = implode("\n", $csvContent);
            $response = Response::make($csvContent, 200, [
              'Content-Type' => 'text/csv',
              'Content-Disposition' => 'attachment; filename="validation_results_' . date('Y-m-d_H-i-s') . '.csv"',
            ]);
            unlink($geojsonPath);

            return $response;
        } else {
            return response()->json(['error' => 'Failed to open the ZIP file'], 400);
        }
    }

    public function uploadKMLFileWithValidation(Request $request)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');
        $rules = [
          'file' => [
              'required',
              'file',
              function ($attribute, $value, $fail) {
                  if (! $value->getClientOriginalName() || ! preg_match('/\.kml$/i', $value->getClientOriginalName())) {
                      $fail('The file must have a .kml extension.');
                  }
              },
          ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $this->parseValidationErrors($validator->errors());

            return response()->json(['errors' => $errors], 422);
        }
        $kmlfile = $request->file('file');
        $tempDir = sys_get_temp_dir();
        $filename = uniqid('kml_file_') . '.' . $kmlfile->getClientOriginalExtension();
        $kmlPath = $tempDir . DIRECTORY_SEPARATOR . $filename;
        $kmlfile->move($tempDir, $filename);
        $geojsonFilename = Str::replaceLast('.kml', '.geojson', $filename);
        $geojsonPath = $tempDir . DIRECTORY_SEPARATOR . $geojsonFilename;
        $process = new Process(['ogr2ogr', '-f', 'GeoJSON', $geojsonPath, $kmlPath]);
        $process->run();
        if (! $process->isSuccessful()) {
            Log::error('Error converting KML to GeoJSON: ' . $process->getErrorOutput());

            return response()->json(['error' => 'Failed to convert KML to GeoJSON', 'message' => $process->getErrorOutput()], 500);
        }
        $csvData = $this->validateGeojson($geojsonPath);
        $csvContent = array_merge([implode(',', array_keys($csvData[0]))], array_map(fn ($row) => implode(',', $row), $csvData));
        $csvContent = implode("\n", $csvContent);
        $response = Response::make($csvContent, 200, [
          'Content-Type' => 'text/csv',
          'Content-Disposition' => 'attachment; filename="validation_results_' . date('Y-m-d_H-i-s') . '.csv"',
        ]);
        unlink($geojsonPath);

        return $response;
    }

    public function validateOverlapping(Request $request)
    {
        $uuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $uuid,
            NotOverlapping::getIntersectionData($uuid),
            PolygonService::OVERLAPPING_CRITERIA_ID
        );
    }

    public function validateEstimatedArea(Request $request)
    {
        $uuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $uuid,
            EstimatedArea::getAreaData($uuid),
            PolygonService::ESTIMATED_AREA_CRITERIA_ID
        );
    }

    public function validateEstimatedAreaProject(Request $request)
    {
        $uuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $uuid,
            EstimatedArea::getAreaDataProject($uuid),
            PolygonService::ESTIMATED_AREA_CRITERIA_ID
        );
    }

    public function validateEstimatedAreaSite(Request $request)
    {
        $uuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $uuid,
            EstimatedArea::getAreaDataSite($uuid),
            PolygonService::ESTIMATED_AREA_CRITERIA_ID
        );
    }

    public function validateCoordinateSystem(Request $request)
    {
        $uuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $uuid,
            ['valid' => FeatureBounds::uuidValid($uuid)],
            PolygonService::COORDINATE_SYSTEM_CRITERIA_ID
        );
    }

    public function getPolygonAsGeoJSONDownload(Request $request)
    {
        try {
            $uuid = $request->query('uuid');

            $polygonGeometry = PolygonGeometry::where('uuid', $uuid)
              ->select(DB::raw('ST_AsGeoJSON(geom) AS geojsonGeom'))
              ->first();

            if (! $polygonGeometry) {
                return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
            }

            $sitePolygon = SitePolygon::where('poly_id', $uuid)->first();
            if (! $sitePolygon) {
                return response()->json(['message' => 'No site polygon found for the given UUID.'], 404);
            }

            $properties = [];
            $fieldsToValidate = [
              'poly_name',
              'plantstart',
              'plantend',
              'practice',
              'target_sys',
              'distr',
              'num_trees',
              'uuid',
              'site_id',
            ];
            foreach ($fieldsToValidate as $field) {
                $properties[$field] = $sitePolygon->$field;
            }

            $propertiesJson = json_encode($properties);

            $feature = [
              'type' => 'Feature',
              'geometry' => json_decode($polygonGeometry->geojsonGeom),
              'properties' => json_decode($propertiesJson),
            ];

            $featureCollection = [
              'type' => 'FeatureCollection',
              'features' => [$feature],
            ];

            return response()->json($featureCollection);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to generate GeoJSON.', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAllPolygonsAsGeoJSONDownload(Request $request)
    {
        try {
            $siteUuid = $request->query('uuid');
            $polygonsUuids = SitePolygon::where('site_id', $siteUuid)
                ->active()
                ->pluck('poly_id');
            $features = [];
            foreach ($polygonsUuids as $polygonUuid) {
                $feature = [];
                $polygonGeometry = PolygonGeometry::where('uuid', $polygonUuid)
                  ->select(DB::raw('ST_AsGeoJSON(geom) AS geojsonGeom'))
                  ->first();
                if (! $polygonGeometry) {
                    return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
                }

                $sitePolygon = SitePolygon::where('poly_id', $polygonUuid)->first();
                if (! $sitePolygon) {
                    return response()->json(['message' => 'No site polygon found for the given UUID.'], 404);
                }

                $properties = [];
                $fieldsToValidate = ['poly_name', 'plantstart', 'plantend', 'practice', 'target_sys', 'distr', 'num_trees', 'site_id', 'uuid'];
                foreach ($fieldsToValidate as $field) {
                    $properties[$field] = $sitePolygon->$field;
                }

                $propertiesJson = json_encode($properties);

                $feature = [
                  'type' => 'Feature',
                  'geometry' => json_decode($polygonGeometry->geojsonGeom),
                  'properties' => json_decode($propertiesJson),
                ];
                $features[] = $feature;
            }
            $featureCollection = [
              'type' => 'FeatureCollection',
              'features' => $features,
            ];

            return response()->json($featureCollection);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to generate GeoJSON.', 'error' => $e->getMessage()], 500);
        }
    }

    public function downloadAllActivePolygonsByFramework(Request $request)
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        $framework = $request->query('framework');

        try {
            $sitesFromFramework = Site::where('framework_key', $framework)->pluck('uuid');

            $activePolygonIds = SitePolygon::wherein('site_id', $sitesFromFramework)->active()->pluck('poly_id');
            Log::info('count of active polygons: ', ['count' => count($activePolygonIds)]);
            $features = [];
            foreach ($activePolygonIds as $polygonUuid) {

                $polygonGeometry = PolygonGeometry::where('uuid', $polygonUuid)
                    ->select(DB::raw('ST_AsGeoJSON(geom) AS geojsonGeom'))
                    ->first();

                if (! $polygonGeometry) {
                    Log::warning('No geometry found for Polygon UUID:', ['uuid' => $polygonUuid]);

                    continue;
                }
                $sitePolygon = SitePolygon::where('poly_id', $polygonUuid)->first();
                $properties = $sitePolygon ? $sitePolygon->only(['poly_name', 'plantstart', 'plantend', 'practice', 'target_sys', 'distr', 'num_trees', 'site_id', 'uuid']) : [];
                $feature = [
                    'type' => 'Feature',
                    'geometry' => json_decode($polygonGeometry->geojsonGeom),
                    'properties' => $properties,
                ];
                $features[] = $feature;
            }
            $featureCollection = [
                'type' => 'FeatureCollection',
                'features' => $features,
            ];

            return response()->json($featureCollection);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to generate GeoJSON.', 'error' => $e->getMessage()], 500);
        }
    }

    public function downloadGeojsonAllActivePolygons()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        try {
            $activePolygonIds = SitePolygon::active()->pluck('poly_id');

            $features = [];
            foreach ($activePolygonIds as $polygonUuid) {

                $polygonGeometry = PolygonGeometry::where('uuid', $polygonUuid)
                    ->select(DB::raw('ST_AsGeoJSON(geom) AS geojsonGeom'))
                    ->first();

                if (! $polygonGeometry) {
                    Log::warning('No geometry found for Polygon UUID:', ['uuid' => $polygonUuid]);

                    continue;
                }
                $sitePolygon = SitePolygon::where('poly_id', $polygonUuid)->first();
                $properties = $sitePolygon ? $sitePolygon->only(['poly_name', 'plantstart', 'plantend', 'practice', 'target_sys', 'distr', 'num_trees', 'site_id', 'uuid']) : [];
                $feature = [
                    'type' => 'Feature',
                    'geometry' => json_decode($polygonGeometry->geojsonGeom),
                    'properties' => $properties,
                ];
                $features[] = $feature;
            }
            $featureCollection = [
                'type' => 'FeatureCollection',
                'features' => $features,
            ];

            return response()->json($featureCollection);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to generate GeoJSON.', 'error' => $e->getMessage()], 500);
        }
    }

    public function GetAllPolygonsLoaded($geojson, $uuid)
    {
        $polygonsUuids = SitePolygon::where('site_id', $uuid)
            ->active()
            ->get();
        $geojson = json_decode($geojson, true);
        $polygonsUuids = collect($polygonsUuids)->toArray();
        $features = collect($geojson['features']);

        $featuresUuids = $features->pluck('properties.uuid')->toArray();
        ;

        foreach ($polygonsUuids as $polygon) {
            $isPresent = in_array($polygon['uuid'], $featuresUuids);
            $response[] = array_merge($polygon, ['is_present' => ! ! $isPresent]);
        }

        return response()->json($response);
    }

    public function getAllCountryNames()
    {
        $countries = WorldCountryGeneralized::select('country')
          ->distinct()
          ->orderBy('country')
          ->pluck('country');

        return response()->json(['countries' => $countries]);
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

            $this->validateOverlapping($request);
            $this->checkSelfIntersection($request);
            $this->validateCoordinateSystem($request);
            $this->validatePolygonSize($request);
            $this->checkWithinCountry($request);
            $this->checkBoundarySegments($request);
            $this->getGeometryType($request);
            $this->validateEstimatedArea($request);
            $this->validateDataInDB($request);
        } catch(\Exception $e) {
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

    public function runSiteValidationPolygon(Request $request)
    {
        try {
            $uuid = $request->input('uuid');

            $sitePolygonsUuids = GeometryHelper::getSitePolygonsUuids($uuid)->toArray();
            $delayedJob = DelayedJob::create();
            $job = new RunSitePolygonsValidationJob($delayedJob->id, $sitePolygonsUuids);
            dispatch($job);

            return (new DelayedJobResource($delayedJob))->additional(['message' => 'Validation completed for all site polygons']);
        } catch (\Exception $e) {
            Log::error('Error during site validation polygon: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during site validation'], 500);
        }
    }

    public function runPolygonsValidation(Request $request)
    {
        try {
            $uuids = $request->input('uuids');
            $delayedJob = DelayedJob::create();
            $job = new RunSitePolygonsValidationJob($delayedJob->id, $uuids);
            dispatch($job);

            return (new DelayedJobResource($delayedJob))->additional(['message' => 'Validation completed for these polygons']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred during validation'], 500);
        }
    }

    public function getCurrentSiteValidation(Request $request)
    {
        try {
            $uuid = $request->input('uuid');
            $cacheValue = Redis::get('dashboard:sitevalidation|'.$uuid);

            if (! $cacheValue) {

                $delayedJob = DelayedJob::create();
                $job = new SiteValidationJob(
                    $uuid
                );
                dispatch($job);

                return (new DelayedJobResource($delayedJob))->additional(['message' => 'Site validation is being processed']);
            } else {
                return response()->json(json_decode($cacheValue));
            }
        } catch (\Exception $e) {
            Log::error('Error during site validation delayed : ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during site validation delayed'. $e->getMessage()], 500);
        }

    }

    private function fetchCriteriaData($polygonUuid)
    {
        $polygonRequest = new Request(['uuid' => $polygonUuid]);
        $criteriaDataResponse = $this->getCriteriaData($polygonRequest);

        return json_decode($criteriaDataResponse->getContent(), true);
    }
}
