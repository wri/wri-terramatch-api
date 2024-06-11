<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Http\Controllers\Controller;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\WorldCountryGeneralized;
use App\Services\PolygonService;
use App\Validators\Extensions\Polygons\EstimatedArea;
use App\Validators\Extensions\Polygons\GeometryType;
use App\Validators\Extensions\Polygons\NotOverlapping;
use App\Validators\Extensions\Polygons\PolygonSize;
use App\Validators\Extensions\Polygons\SelfIntersection;
use App\Validators\Extensions\Polygons\Spikes;
use App\Validators\Extensions\Polygons\WithinCountry;
use App\Validators\SitePolygonValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

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

    /**
     * @throws ValidationException
     */
    public function insertGeojsonToDB(string $geojsonFilename, ?string $site_id = null)
    {
        $tempDir = sys_get_temp_dir();
        $geojsonPath = $tempDir . DIRECTORY_SEPARATOR . $geojsonFilename;
        $geojsonData = file_get_contents($geojsonPath);
        $geojson = json_decode($geojsonData, true);

        SitePolygonValidator::validate('FEATURE_BOUNDS', $geojson, false);

        return App::make(PolygonService::class)->createGeojsonModels($geojson, ['site_id' => $site_id]);
    }

    public function validateDataInDB(Request $request)
    {
        $polygonUuid = $request->input('uuid');
        $fieldsToValidate = ['poly_name', 'plantstart', 'plantend', 'practice', 'target_sys', 'distr', 'num_trees'];
        // Check if the polygon with the specified poly_id exists
        $polygonExists = SitePolygon::forPolygonGeometry($polygonUuid)->exists();

        if (! $polygonExists) {
            return response()->json(['valid' => false, 'message' => 'No site polygon found with the specified poly_id.']);
        }

        // Proceed with validation of attribute values
        $whereConditions = [];
        foreach ($fieldsToValidate as $field) {
            $whereConditions[] = "(IFNULL($field, '') = '' OR $field IS NULL)";
        }

        $sitePolygonData = SitePolygon::forPolygonGeometry($polygonUuid)
            ->where(function ($query) use ($whereConditions) {
                foreach ($whereConditions as $condition) {
                    $query->orWhereRaw($condition);
                }
            })
            ->first();
        $valid = $sitePolygonData == null;
        $responseData = ['valid' => $valid];
        if (! $valid) {
            $responseData['message'] = 'Some attributes of the site polygon are invalid.';
        }

        App::make(PolygonService::class)
            ->createCriteriaSite($polygonUuid, PolygonService::DATA_CRITERIA_ID, $valid);

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

    public function uploadKMLFile(Request $request)
    {
        if ($request->hasFile('file')) {
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
            $uuid = $this->insertGeojsonToDB($geojsonFilename, $site_id);
            if (isset($uuid['error'])) {
                return response()->json(['error' => 'Geometry not inserted into DB', 'message' => $uuid['error']], 500);
            }

            return response()->json(['message' => 'KML file processed and inserted successfully', 'uuid' => $uuid], 200);
        } else {
            return response()->json(['error' => 'KML file not provided'], 400);
        }
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

    public function uploadShapefile(Request $request)
    {
        Log::debug('Upload Shape file data', ['request' => $request->all()]);
        if ($request->hasFile('file')) {
            $site_id = $request->input('uuid');
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
                $uuid = $this->insertGeojsonToDB($geojsonFilename, $site_id);
                if (isset($uuid['error'])) {
                    return response()->json(['error' => 'Geometry not inserted into DB', 'message' => $uuid['error']], 500);
                }

                return response()->json(['message' => 'Shape file processed and inserted successfully', 'uuid' => $uuid], 200);
            } else {
                return response()->json(['error' => 'Failed to open the ZIP file'], 400);
            }
        } else {
            return response()->json(['error' => 'No file uploaded'], 400);
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
            $valid = $geometryType === GeometryType::VALID_TYPE;
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

        // Fetch data from criteria_site with distinct criteria_id based on the latest created_at
        $criteriaDataQuery = 'SELECT criteria_id, MAX(created_at) AS latest_created_at
                          FROM criteria_site 
                          WHERE polygon_id = ?
                          GROUP BY criteria_id';

        $criteriaData = DB::select($criteriaDataQuery, [$uuid]);

        if (empty($criteriaData)) {
            return response()->json(['error' => 'Criteria data not found for the given polygon ID'], 404);
        }

        // Determine the validity of each criteria
        $criteriaList = [];
        foreach ($criteriaData as $criteria) {
            $criteriaId = $criteria->criteria_id;
            $valid = CriteriaSite::where(['polygon_id' => $uuid, 'criteria_id' => $criteriaId])->orderBy('created_at', 'desc')->select('valid')->first()?->valid;
            $criteriaList[] = [
              'criteria_id' => $criteriaId,
              'latest_created_at' => $criteria->latest_created_at,
              'valid' => $valid,
            ];
        }

        return response()->json(['polygon_id' => $uuid, 'criteria_list' => $criteriaList]);
    }

    public function uploadGeoJSONFile(Request $request)
    {
        if ($request->hasFile('file')) {
            $site_id = $request->input('uuid');
            $file = $request->file('file');
            $tempDir = sys_get_temp_dir();
            $filename = uniqid('geojson_file_') . '.' . $file->getClientOriginalExtension();
            $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;
            $file->move($tempDir, $filename);
            $uuid = $this->insertGeojsonToDB($filename, $site_id);
            if (is_array($uuid) && isset($uuid['error'])) {
                return response()->json(['error' => 'Failed to insert GeoJSON data into the database', 'message' => $uuid['error']], 500);
            }

            return response()->json(['message' => 'Geojson file processed and inserted successfully', 'uuid' => $uuid], 200);
        } else {
            return response()->json(['error' => 'GeoJSON file not provided in request'], 400);
        }
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

    public function getPolygonAsGeoJSONDownload(Request $request)
    {
        try {
            $uuid = $request->query('uuid');

            $polygonGeometry = PolygonGeometry::where('uuid', $uuid)
            ->select(DB::raw('ST_AsGeoJSON(geom) AS geojsonGeom'))
            ->first();

            Log::info('Polygon Geometry', ['polygonGeometry' => $polygonGeometry]);
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
            $polygonsUuids = SitePolygon::where('site_id', $siteUuid)->pluck('poly_id');
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
                $fieldsToValidate = ['poly_name', 'plantstart', 'plantend', 'practice', 'target_sys', 'distr', 'num_trees', 'site_id'];
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

        $response['insertion_success'] = App::make(PolygonService::class)
            ->createCriteriaSite($polygonUuid, $criteriaId, $response['valid']);

        return response()->json($response);
    }

    public function runValidationPolygon(string $uuid)
    {
        $request = new Request(['uuid' => $uuid]);

        $this->validateOverlapping($request);
        $this->checkSelfIntersection($request);
        $this->validatePolygonSize($request);
        $this->checkWithinCountry($request);
        $this->checkBoundarySegments($request);
        $this->getGeometryType($request);
        $this->validateEstimatedArea($request);
        $this->validateDataInDB($request);
    }

    public function getValidationPolygon(Request $request)
    {

        $uuid = $request->input('uuid');
        $this->runValidationPolygon($uuid);
        $criteriaData = $this->getCriteriaData($request);

        return $criteriaData;
    }

    public function getSiteValidationPolygon(Request $request)
    {
        try {
            $uuid = $request->input('uuid');

            $sitePolygonsUuids = $this->getSitePolygonsUuids($uuid);

            foreach ($sitePolygonsUuids as $polygonUuid) {
                $this->runValidationPolygon($polygonUuid);
            }

            return response()->json(['message' => 'Validation completed for all site polygons']);
        } catch (\Exception $e) {
            Log::error('Error during site validation polygon: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during site validation'], 500);
        }
    }

    public function getCurrentSiteValidation(Request $request)
    {
        try {
            $uuid = $request->input('uuid');

            $sitePolygonsUuids = $this->getSitePolygonsUuids($uuid);
            $checkedPolygons = [];

            foreach ($sitePolygonsUuids as $polygonUuid) {
                $isValid = true;
                $isChecked = true;

                $criteriaData = $this->fetchCriteriaData($polygonUuid);

                if (isset($criteriaData['error'])) {
                    Log::error('Error fetching criteria data', ['polygon_uuid' => $polygonUuid, 'error' => $criteriaData['error']]);
                    $isValid = false;
                    $isChecked = false;
                } else {
                    foreach ($criteriaData['criteria_list'] as $criteria) {
                        if ($criteria['valid'] == 0) {
                            $isValid = false;

                            break;
                        }
                    }
                }

                $checkedPolygons[] = [
                    'uuid' => $polygonUuid,
                    'valid' => $isValid,
                    'checked' => $isChecked,
                ];
            }

            return $checkedPolygons;
        } catch (\Exception $e) {
            Log::error('Error during current site validation: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during current site validation'], 500);
        }
    }

    private function getSitePolygonsUuids($uuid)
    {
        return SitePolygon::where('site_id', $uuid)->get()->pluck('poly_id');
    }

    private function fetchCriteriaData($polygonUuid)
    {
        $polygonRequest = new Request(['uuid' => $polygonUuid]);
        $criteriaDataResponse = $this->getCriteriaData($polygonRequest);

        return json_decode($criteriaDataResponse->getContent(), true);
    }
}
