<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\V2\PolygonGeometry;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\WorldCountryGeneralized;
use App\Models\V2\Sites\SitePolygon;


class TerrafundCreateGeometryController extends Controller
{
  public function processGeometry(string $uuid)
  {
    $geometry = PolygonGeometry::where('uuid', $uuid)
      ->select(DB::raw('ST_AsGeoJSON(geom) AS geojson'))
      ->first();

    $geojson = $geometry->geojson;

    if ($geojson) {
      return response()->json(['geometry' => $geojson], 200);
    } else {
      return response()->json(['error' => 'Geometry not found'], 404);
    }
  }

  public function storeGeometry(Request $request)
  {
    $validatedData = $request->validate([
      'geometry' => 'required|json',
    ]);
    $geometry = json_decode($request->input('geometry'));
    $geom = DB::raw("ST_GeomFromGeoJSON('" . json_encode($geometry) . "')");
    $uuid = Str::uuid();

    DB::table('polygon_geometry')->insert([
      'uuid' => $uuid,
      'geom' => $geom,
      'created_at' => now(),
      'updated_at' => now(),
    ]);
    return response()->json(['uuid' => $uuid], 200);
  }
  private function insertSinglePolygon(array $geometry, int $srid)
  {
    try {
      // Convert geometry to GeoJSON string with specified SRID
      $geojson = json_encode(['type' => 'Feature', 'geometry' => $geometry, 'crs' => ['type' => 'name', 'properties' => ['name' => "EPSG:$srid"]]]);

      // Insert GeoJSON data into the database
      $geom = DB::raw("ST_GeomFromGeoJSON('$geojson')");
      $uuid = Str::uuid();
      $areaSqDegrees = DB::selectOne("SELECT ST_Area(ST_GeomFromGeoJSON('$geojson')) AS area")->area;
      $latitude = DB::selectOne("SELECT ST_Y(ST_Centroid(ST_GeomFromGeoJSON('$geojson'))) AS latitude")->latitude;
      $areaSqMeters = $areaSqDegrees * pow(111320 * cos(deg2rad($latitude)), 2);

      $areaHectares = $areaSqMeters / 10000;

      $id = DB::table('polygon_geometry')->insertGetId([
        'uuid' => $uuid,
        'geom' => $geom,
        'created_at' => now(),
        'updated_at' => now(),
      ]);
      return ['uuid' => $uuid, 'id' => $id, 'area' => $areaHectares];
    } catch (\Exception $e) {
      echo $e;
      return $e->getMessage();
    }
  }
  private function validatePolygonBounds(array $geometry): bool {
    if ($geometry['type'] !== 'Polygon') {
        return false;
    }
    $coordinates = $geometry['coordinates'][0];
    foreach ($coordinates as $coordinate) {
        $latitude = $coordinate[1];
        $longitude = $coordinate[0];

        // Check latitude bounds
        if ($latitude < -90 || $latitude > 90) {
            return false;
        }

        // Check longitude bounds
        if ($longitude < -180 || $longitude > 180) {
            return false;
        }
    }

    return true;
}
  public function insertGeojsonToDB(string $geojsonFilename)
  {
    $srid = 4326;
    $geojsonData = Storage::get("public/geojson_files/{$geojsonFilename}");
    $geojson = json_decode($geojsonData, true);
    if (!isset($geojson['features'])) {
      return ['error' => 'GeoJSON file does not contain features'];
    }
    $uuids = [];
    foreach ($geojson['features'] as $feature) {
      if ($feature['geometry']['type'] === 'Polygon') {
        if (!$this->validatePolygonBounds($feature['geometry'])) {
          return ['error' => 'Invalid polygon bounds'];
        }
        $data = $this->insertSinglePolygon($feature['geometry'], $srid);
        $uuids[] = $data['uuid'];
        $returnSite = $this->insertSitePolygon($data['uuid'], $feature['properties'], $data['area']);
      } elseif ($feature['geometry']['type'] === 'MultiPolygon') {
        foreach ($feature['geometry']['coordinates'] as $polygon) {
          if (!$this->validatePolygonBounds($feature['geometry'])) {
            return ['error' => 'Invalid polygon bounds'];
          }
          $data = $this->insertSinglePolygon(['type' => 'Polygon', 'coordinates' => $polygon], $srid);
          $uuids[] = $data['uuid'];
          $returnSite = $this->insertSitePolygon($data['uuid'], $feature['properties'], $data['area']);
        }
      }
    }
    return $uuids;
  }
  private function validateSchema(array $properties, array $fields): bool
  {
    foreach ($fields as $field) {
      if (!array_key_exists($field, $properties)) {
        return false;
      }
    }
    return true;
  }
  public function validateDataInDB(Request $request) {
    $polygonUuid = $request->input('uuid');
    $fieldsToValidate = ['poly_name', 'plantstart', 'plantend', 'practice', 'target_sys', 'distr', 'num_trees'];
    $DATA_CRITERIA_ID = 14;
    // Check if the polygon with the specified poly_id exists
    $polygonExists = DB::table('site_polygon')
        ->where('poly_id', $polygonUuid)
        ->exists();
    $valid = false;
    if (!$polygonExists) {
        return response()->json(['valid' => $valid, 'message' => 'No site polygon found with the specified poly_id.']);
    }

    // Proceed with validation of attribute values
    $whereConditions = [];
    foreach ($fieldsToValidate as $field) {
        $whereConditions[] = "(IFNULL($field, '') = '' OR $field IS NULL)";
    }

    $sitePolygonData = DB::table('site_polygon')
        ->where('poly_id', $polygonUuid)
        ->where(function($query) use ($whereConditions) {
            foreach ($whereConditions as $condition) {
                $query->orWhereRaw($condition);
            }
        })
        ->first();
    $this->insertCriteriaSite($polygonUuid, $DATA_CRITERIA_ID, $valid);
    if ($sitePolygonData) {
        return response()->json(['valid' => $valid, 'message' => 'Some attributes of the site polygon are invalid.']);
    }

    $valid = true;
    $this->insertCriteriaSite($polygonUuid, $DATA_CRITERIA_ID, $valid);
    return response()->json(['valid' => $valid]);
}


  private function validateData(array $properties, array $fields): bool
  {
    foreach ($fields as $field) {
      $value = $properties[$field];
      if ($value === null || strtoupper($value) === 'NULL' || $value === '') {
        return false;
      }
    }
    return true;
  }
  private function insertSitePolygon(string $polygonUuid, array $properties, float $area)
  {
    try {
      $fieldsToValidate = ['poly_name', 'plantstart', 'plantend', 'practice', 'target_sys', 'distr', 'num_trees'];
      $SCHEMA_CRITERIA_ID = 13;
      $validSchema = true;
      $DATA_CRITERIA_ID = 14;
      $validData = true;
      if (!$this->validateSchema($properties, $fieldsToValidate)) {
        $validSchema = false;
        $validData = false;
      } else if (!$this->validateData($properties, $fieldsToValidate)) {
        $validData = false;
      }
      $insertionSchemaSuccess = $this->insertCriteriaSite($polygonUuid, $SCHEMA_CRITERIA_ID, $validSchema);
      $insertionDataSuccess = $this->insertCriteriaSite($polygonUuid, $DATA_CRITERIA_ID, $validData);

      $sitePolygon = new SitePolygon();
      $sitePolygon->uuid = Str::uuid();
      $sitePolygon->project_id = $properties['project_id'] ?? null;
      $sitePolygon->proj_name = $properties['proj_name'] ?? null;
      $sitePolygon->org_name = $properties['org_name'] ?? null;
      $sitePolygon->country = $properties['country'] ?? null;
      $sitePolygon->poly_id = $polygonUuid ?? null;
      $sitePolygon->poly_name = $properties['poly_name'] ?? null;
      $sitePolygon->site_id = $properties['site_id'] ?? null;
      $sitePolygon->site_name = $properties['site_name'] ?? null;
      $sitePolygon->poly_label = $properties['poly_label'] ?? null;
      $sitePolygon->plantstart = !empty($properties['plantstart']) ? $properties['plantstart'] : null;
      $sitePolygon->plantend = !empty($properties['plantend']) ? $properties['plantend'] : null;
      $sitePolygon->practice = $properties['practice'] ?? null;
      $sitePolygon->target_sys = $properties['target_sys'] ?? null;
      $sitePolygon->distr = $properties['distr'] ?? null;
      $sitePolygon->num_trees = $properties['num_trees'] ?? null;
      $sitePolygon->est_area = $area ?? null;
      $sitePolygon->created_at = now();
      $sitePolygon->updated_at = now();
      $sitePolygon->save();
      return 'has saved correctly';
    } catch (\Exception $e) {
      return $e->getMessage();
    }
  }



  public function getGeometryProperties(string $geojsonFilename)
  {
    $geojsonData = Storage::get("public/geojson_files/{$geojsonFilename}");
    $geojson = json_decode($geojsonData, true);
    if (!isset($geojson['features'])) {
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
      $kmlfile = $request->file('file');
      $directory = storage_path('app/public/kml_files');
      if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
      }
      $filename = uniqid('kml_file_') . '.' . $kmlfile->getClientOriginalExtension();
      $kmlfile->move($directory, $filename);
      $geojsonFilename = Str::replaceLast('.kml', '.geojson', $filename);
      $geojsonPath = storage_path("app/public/geojson_files/{$geojsonFilename}");
      $kmlPath = storage_path("app/public/kml_files/{$filename}");
      $process = new Process(['ogr2ogr', '-f', 'GeoJSON', $geojsonPath, $kmlPath]);
      $process->run();
      if (!$process->isSuccessful()) {
        Log::error('Error converting KML to GeoJSON: ' . $process->getErrorOutput());
        return response()->json(['error' => 'Failed to convert KML to GeoJSON', 'message' => $process->getErrorOutput()], 500);
      }
      $uuid = $this->insertGeojsonToDB($geojsonFilename);
      if (isset($uuid['error'])) {
          return response()->json(['error' => 'Geometry not inserted into DB', 'message' => $uuid['error']], 500);
        }
      return response()->json(['message' => 'KML file processed and inserted successfully', 'uuid' => $uuid], 200);
    } else {
      return response()->json(['error' => 'KML file not provided'], 400);
    }
  }


  public function uploadShapefile(Request $request)
  {
    if ($request->hasFile('file')) {
      $file = $request->file('file');
      if ($file->getClientOriginalExtension() !== 'zip') {
        return response()->json(['error' => 'Only ZIP files are allowed'], 400);
      }
      $directory = storage_path('app/public/shapefiles/' . uniqid('shapefile_'));
      mkdir($directory, 0755, true);

      // Extract the contents of the ZIP file
      $zip = new \ZipArchive();
      if ($zip->open($file->getPathname()) === true) {
        $zip->extractTo($directory);
        $zip->close();
        $shpFile = $this->findShpFile($directory);
        if (!$shpFile) {
          return response()->json(['error' => 'Shapefile (.shp) not found in the ZIP file'], 400);
        }
        $geojsonFilename = Str::replaceLast('.shp', '.geojson', basename($shpFile));
        $geojsonPath = storage_path("app/public/geojson_files/{$geojsonFilename}");
        $process = new Process(['ogr2ogr', '-f', 'GeoJSON', $geojsonPath, $shpFile]);
        $process->run();
        if (!$process->isSuccessful()) {
          Log::error('Error converting Shapefile to GeoJSON: ' . $process->getErrorOutput());
          return response()->json(['error' => 'Failed to convert Shapefile to GeoJSON', 'message' => $process->getErrorOutput()], 500);
        }
        $uuid = $this->insertGeojsonToDB($geojsonFilename);
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

  private function findShpFile($directory)
  {
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

  public function checkSelfIntersection(Request $request)
  {
    $uuid = $request->query('uuid');
    $geometry = PolygonGeometry::where('uuid', $uuid)->first();

    if (!$geometry) {
      return response()->json(['error' => 'Geometry not found'], 404);
    }

    $isSimple = DB::selectOne("SELECT ST_IsSimple(geom) AS is_simple FROM polygon_geometry WHERE uuid = :uuid", ['uuid' => $uuid])->is_simple;
    $SELF_CRITERIA_ID = 4;
    $message = $isSimple ? 'The geometry is valid' : 'The geometry has self-intersections';
    $insertionSuccess = $this->insertCriteriaSite($uuid, $SELF_CRITERIA_ID, $isSimple);
    return response()->json(['selfintersects' => $message, 'geometry_id' => $geometry->id, 'insertion_success' => $insertionSuccess, 'valid' => $isSimple ? true : false], 200);
  }
  public function calculateDistance($point1, $point2)
  {
    $lat1 = $point1[1];
    $lon1 = $point1[0];
    $lat2 = $point2[1];
    $lon2 = $point2[0];

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    return $miles * 1.609344; // Convert to kilometers
  }
  // public function detectSpikes($geometry)
  // {
  //   $spikes = [];
  //   if ($geometry['type'] === 'Polygon' || $geometry['type'] === 'MultiPolygon') {
  //     $coordinates = $geometry['type'] === 'Polygon' ? $geometry['coordinates'][0] : $geometry['coordinates'][0][0]; // First ring of the polygon or the first polygon in the MultiPolygon
  //     $numVertices = count($coordinates);
  //     $perimeter = 0;
  //     for ($i = 0; $i < $numVertices - 1; $i++) {
  //       $distance = $this->calculateDistance($coordinates[$i], $coordinates[$i + 1]);
  //       $perimeter += $distance;
  //     }
  //     $averageDistance = $perimeter / ($numVertices - 1);
  //     $threshold = $averageDistance * 1.25; // Adjust this threshold as needed
  //     for ($i = 0; $i < $numVertices - 1; $i++) {
  //       $distance = $this->calculateDistance($coordinates[$i], $coordinates[$i + 1]);
  //       if (abs($distance - $averageDistance) > $threshold) {
  //         $spikes[] = $coordinates[$i];
  //       }
  //     }
  //   }
  //   return $spikes;
  // }
  public function detectSpikes($geometry)
{
    $spikes = [];

    if ($geometry['type'] === 'Polygon' || $geometry['type'] === 'MultiPolygon') {
        $coordinates = $geometry['type'] === 'Polygon' ? $geometry['coordinates'][0] : $geometry['coordinates'][0][0]; // First ring of the polygon or the first polygon in the MultiPolygon
        $numVertices = count($coordinates);
        $totalDistance = 0;

        for ($i = 0; $i < $numVertices - 1; $i++) {
            $totalDistance += $this->calculateDistance($coordinates[$i], $coordinates[$i + 1]);
        }

        for ($i = 0; $i < $numVertices - 1; $i++) {
            $distance1 = $this->calculateDistance($coordinates[$i], $coordinates[($i + 1) % $numVertices]);
            $distance2 = $this->calculateDistance($coordinates[($i + 1) % $numVertices], $coordinates[($i + 2) % $numVertices]);
            $combinedDistance = $distance1 + $distance2;

            if ($combinedDistance > 0.6 * $totalDistance) {
                // Vertex and its adjacent vertices contribute more than 25% of the total boundary path distance
                $spikes[] = $coordinates[($i + 1) % $numVertices];
            }
        }
    }

    return $spikes;
}
  public function insertCriteriaSite($POLYGON_ID, $CRITERIA_ID, $valid)
  {
    $criteriaSite = new CriteriaSite();
    $criteriaSite->polygon_id = $POLYGON_ID;
    $criteriaSite->criteria_id = $CRITERIA_ID;
    $criteriaSite->uuid = Str::uuid();
    $criteriaSite->valid = $valid;
    try {
      $criteriaSite->save();
      return true;
    } catch (\Exception $e) {
      return $e->getMessage();
    }
  }

  public function checkBoundarySegments(Request $request)
  {
    $uuid = $request->query('uuid');
    $geometry = PolygonGeometry::where('uuid', $uuid)->first();

    if (!$geometry) {
      return response()->json(['error' => 'Geometry not found'], 404);
    }
    $geojson = DB::selectOne("SELECT ST_AsGeoJSON(geom) AS geojson FROM polygon_geometry WHERE uuid = :uuid", ['uuid' => $uuid])->geojson;
    $geojsonArray = json_decode($geojson, true);
    $spikes = $this->detectSpikes($geojsonArray);
    $SPIKE_CRITERIA_ID = 8;
    $valid = count($spikes) === 0;
    $insertionSuccess = $this->insertCriteriaSite($uuid, $SPIKE_CRITERIA_ID, $valid);
    return response()->json(['spikes' => $spikes, 'geometry_id' => $uuid, 'insertion_success' => $insertionSuccess, 'valid' => $valid], 200);
  }
  public function validatePolygonSize(Request $request)
  {
    $uuid = $request->query('uuid');
    $geometry = PolygonGeometry::where('uuid', $uuid)->first();

    if (!$geometry) {
      return response()->json(['error' => 'Geometry not found'], 404);
    }

    $areaSqDegrees = DB::selectOne("SELECT ST_Area(geom) AS area FROM polygon_geometry WHERE uuid = :uuid", ['uuid' => $uuid])->area;
    $latitude = DB::selectOne("SELECT ST_Y(ST_Centroid(geom)) AS latitude FROM polygon_geometry WHERE uuid = :uuid", ['uuid' => $uuid])->latitude;
    $areaSqMeters = $areaSqDegrees * pow(111320 * cos(deg2rad($latitude)), 2);
    $SIZE_CRITERIA_ID = 6;
    $valid = $areaSqMeters <= 10000000;
    $insertionSuccess = $this->insertCriteriaSite($uuid, $SIZE_CRITERIA_ID, $valid);
    return response()->json([
      'area_hectares' => $areaSqMeters / 10000, // Convert to hectares
      'area_sqmeters' => $areaSqMeters,
      'geometry_id' => $geometry->id,
      'insertion_success' => $insertionSuccess,
      'valid' => $valid
    ], 200);
  }

  public function checkWithinCountry(Request $request)
  {
      $polygonUuid = $request->input('uuid');
  
      if ($polygonUuid === null || $polygonUuid === '') {
          return response()->json(['error' => 'UUID not provided'], 200);
      }
  
      $geometry = PolygonGeometry::where('uuid', $polygonUuid)->first();
  
      if (!$geometry) {
          return response()->json(['error' => 'Geometry not found'], 404);
      }
  
      $totalArea = DB::table('polygon_geometry')
          ->where('uuid', $polygonUuid)
          ->selectRaw('ST_Area(geom) AS area')
          ->first()->area;
  
      // Find site_polygon_id and project_id using the polygonUuid
      $sitePolygonData = SitePolygon::where('poly_id', $polygonUuid)
          ->select('id', 'project_id')
          ->first();
  
      if (!$sitePolygonData) {
          return response()->json(['error' => 'Site polygon data not found for the specified polygonUuid'], 404);
      }
  
      // Find the country ISO using project_id from v2projects
      $countryIso = DB::table('v2_projects')
          ->where('uuid', $sitePolygonData->project_id)
          ->value('country');
  
      if (!$countryIso) {
          return response()->json(['error' => 'Country ISO not found for the specified project_id'], 404);
      }
  
      $intersectionData = DB::table('world_countries_generalized')
        ->where('iso', $countryIso)
        ->selectRaw('world_countries_generalized.country AS country, ST_Area(ST_Intersection(world_countries_generalized.geometry, (SELECT geom FROM polygon_geometry WHERE uuid = ?))) AS area', [$polygonUuid])
        ->first();

      $intersectionArea = $intersectionData->area;
      $countryName = $intersectionData->country;

      $insidePercentage = $intersectionArea / $totalArea * 100;

      $insideThreshold = 75;
      $insideViolation = $insidePercentage < $insideThreshold;
      $WITHIN_COUNTRY_CRITERIA_ID = 7;
      $insertionSuccess = $this->insertCriteriaSite($polygonUuid, $WITHIN_COUNTRY_CRITERIA_ID, !$insideViolation);

      return response()->json([
          'country_name' => $countryName,
          'inside_percentage' => $insidePercentage,
          'valid' => !$insideViolation,
          'geometry_id' => $geometry->id,
          'insertion_success' => $insertionSuccess
      ]);

  }
  
  public function getGeometryType(Request $request)
  {
    $uuid = $request->input('uuid');

    // Fetch the geometry type based on the UUID using SQL query
    $query = "SELECT ST_GeometryType(geom) AS geometry_type FROM polygon_geometry WHERE uuid = ?";
    $result = DB::selectOne($query, [$uuid]);

    if ($result) {
      $geometryType = $result->geometry_type;
      $valid = $geometryType === 'POLYGON' ? true : false;
      $GEOMETRY_TYPE_CRITERIA_ID = 10;
      $insertionSuccess = $this->insertCriteriaSite($uuid, $GEOMETRY_TYPE_CRITERIA_ID, $valid);
      return response()->json(['uuid' => $uuid, 'geometry_type' => $geometryType, 'valid' => $valid, 'insertion_success' => $insertionSuccess]);
    } else {
      return response()->json(['error' => 'Geometry not found for the given UUID'], 404);
    }
  }


  public function getCriteriaData(Request $request)
  {
    $uuid = $request->input('uuid');

    // Find the ID of the polygon based on the UUID
    $polygonIdQuery = "SELECT id FROM polygon_geometry WHERE uuid = ?";
    $polygonIdResult = DB::selectOne($polygonIdQuery, [$uuid]);

    if (!$polygonIdResult) {
      return response()->json(['error' => 'Polygon not found for the given UUID'], 404);
    }

    // Fetch data from criteria_site with distinct criteria_id based on the latest created_at
    $criteriaDataQuery = "SELECT criteria_id, MAX(created_at) AS latest_created_at
                          FROM criteria_site 
                          WHERE polygon_id = ?
                          GROUP BY criteria_id";

    $criteriaData = DB::select($criteriaDataQuery, [$uuid]);

    if (empty($criteriaData)) {
      return response()->json(['error' => 'Criteria data not found for the given polygon ID'], 404);
    }

    // Determine the validity of each criteria
    $criteriaList = [];
    foreach ($criteriaData as $criteria) {
      $criteriaId = $criteria->criteria_id;

      // Check if the criteria is valid
      $validCriteriaQuery = "SELECT valid FROM criteria_site 
                               WHERE polygon_id = ? AND criteria_id = ?";
      $validResult = DB::selectOne($validCriteriaQuery, [$uuid, $criteriaId]);

      $valid = $validResult ? $validResult->valid : null;

      $criteriaList[] = [
        'criteria_id' => $criteriaId,
        'latest_created_at' => $criteria->latest_created_at,
        'valid' => $valid
      ];
    }

    return response()->json(['polygon_id' => $uuid, 'criteria_list' => $criteriaList]);
  }


  public function uploadGeoJSONFile(Request $request)
  {
    if ($request->hasFile('file')) {
      $file = $request->file('file');
      $directory = storage_path('app/public/geojson_files');
      if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
      }
      $filename = uniqid('geojson_file_') . '.' . $file->getClientOriginalExtension();
      $file->move($directory, $filename);
      $uuid = $this->insertGeojsonToDB($filename);
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
    $sitePolygon = DB::table('site_polygon')
      ->where('poly_id', $uuid)
      ->first();

    if (!$sitePolygon) {
      return response()->json(['error' => 'Site polygon not found for the given polygon ID'], 200);
    }

    $projectId = $sitePolygon->project_id;
    if(!$projectId) {
      return response()->json(['error' => 'Project ID not found for the given polygon ID'], 200);
    }
    $relatedPolyIds = DB::table('site_polygon')
      ->where('project_id', $projectId)
      ->where('poly_id', '!=', $uuid)
      ->pluck('poly_id');

    $intersects = DB::table('polygon_geometry')
      ->whereIn('uuid', $relatedPolyIds)
      ->selectRaw('ST_Intersects(geom, (SELECT geom FROM polygon_geometry WHERE uuid = ?)) as intersects', [$uuid])
      ->get()
      ->pluck('intersects');

    $intersects = in_array(1, $intersects->toArray());
    $valid = !$intersects;
    $OVERLAPPING_CRITERIA_ID = 3;
    $insertionSuccess = $this->insertCriteriaSite($uuid, $OVERLAPPING_CRITERIA_ID, $valid);
    return response()->json(['intersects' => $intersects, 'project_id' => $projectId, 'uuid' => $uuid, 'valid' => $valid, 'creteria_succes' => $insertionSuccess], 200);
  }
  public function validateEstimatedArea(Request $request)
  {
    $uuid = $request->input('uuid');
    $sitePolygon = DB::table('site_polygon')
      ->where('poly_id', $uuid)
      ->first();

    if (!$sitePolygon) {
      return response()->json(['error' => 'Site polygon not found for the given polygon ID'], 200);
    }

    $projectId = $sitePolygon->project_id;

    $sumEstArea = DB::table('site_polygon')
      ->where('project_id', $projectId)
      ->sum('est_area');

    $project = DB::table('v2_projects')
      ->where('uuid', $projectId)
      ->first();

    if (!$project) {
      return response()->json(['error' => 'Project not found for the given project ID', 'projectId' => $projectId], 200);
    }

    $totalHectaresRestoredGoal = $project->total_hectares_restored_goal;
    if ($totalHectaresRestoredGoal === null || $totalHectaresRestoredGoal === 0) {
      return response()->json(['error' => 'Total hectares restored goal not set for the project'], 400);
    }
    $lowerBound = 0.75 * $totalHectaresRestoredGoal;
    $upperBound = 1.25 * $totalHectaresRestoredGoal;
    $valid = false;
    if ($sumEstArea >= $lowerBound && $sumEstArea <= $upperBound) {
      $valid = true;
    }
    $ESTIMATED_AREA_CRITERIA_ID = 12;
    $insertionSuccess = $this->insertCriteriaSite($uuid, $ESTIMATED_AREA_CRITERIA_ID, $valid);
    return response()->json(['valid' => $valid, 'sum_area_project' => $sumEstArea, 'total_area_project' => $totalHectaresRestoredGoal, 'insertionSuccess' => $insertionSuccess], 200);
  }

  

  public function getPolygonsAsGeoJSON()
  {
    $limit = 2;
    $polygons = DB::table('polygon_geometry')
      ->select(DB::raw('ST_AsGeoJSON(geom) AS geojson'))
      ->orderBy('created_at', 'desc')
      ->whereNotNull('geom')
      ->limit($limit)
      ->get();
    $features = [];

    foreach ($polygons as $polygon) {
      $coordinates = json_decode($polygon->geojson)->coordinates;
      $feature = [
        'type' => 'Feature',
        'geometry' => [
          'type' => 'Polygon',
          'coordinates' => $coordinates,
        ],
        'properties' => [],
      ];
      $features[] = $feature;
    }
    $geojson = [
      'type' => 'FeatureCollection',
      'features' => $features,
    ];

    // Return the GeoJSON data
    return response()->json($geojson);
  }
  public function getAllCountryNames()
  {
    $countries = WorldCountryGeneralized::select('country')
      ->distinct()
      ->orderBy('country')
      ->pluck('country');
    return response()->json(['countries' => $countries]);
  }



}
