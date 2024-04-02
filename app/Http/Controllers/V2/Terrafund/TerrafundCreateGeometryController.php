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


class TerrafundCreateGeometryController extends Controller
{
    public function processGeometry(string $uuid) {
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
        // Convert geometry to GeoJSON string with specified SRID
        $geojson = json_encode(['type' => 'Feature', 'geometry' => $geometry, 'crs' => ['type' => 'name', 'properties' => ['name' => "EPSG:$srid"]]]);
    
        // Insert GeoJSON data into the database
        $geom = DB::raw("ST_GeomFromGeoJSON('$geojson')");
        $uuid = Str::uuid();
    
        DB::table('polygon_geometry')->insert([
            'uuid' => $uuid,
            'geom' => $geom,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return $uuid;
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
                $uuids[] = $this->insertSinglePolygon($feature['geometry'], $srid);
            } elseif ($feature['geometry']['type'] === 'MultiPolygon') {
                foreach ($feature['geometry']['coordinates'] as $polygon) {
                    $uuids[] = $this->insertSinglePolygon(['type' => 'Polygon', 'coordinates' => $polygon], $srid);
                }
            }
        }
        return $uuids;
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

            // Extract properties only if the geometry type is Polygon or MultiPolygon
            if ($geometryType === 'Polygon' || $geometryType === 'MultiPolygon') {
                $propertiesList[] = $properties;
            }
        }

        return $propertiesList;
    }
    public function uploadKMLFile(Request $request) {
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
              return response()->json(['error' => 'Failed to convert KML to GeoJSON'], 500);
          }
          $uuid = $this->insertGeojsonToDB($geojsonFilename);
          $properties = $this->getGeometryProperties($geojsonFilename);
          return response()->json(['message' => 'KML file processed and inserted successfully', 'uuid' => $uuid, 'properties' => $properties], 200);
      
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
                  return response()->json(['error' => 'Failed to convert Shapefile to GeoJSON'], 500);
              }
  
              // Insert GeoJSON data into the database
              $uuid = $this->insertGeojsonToDB($geojsonFilename);
              $properties = $this->getGeometryProperties($geojsonFilename);
              return response()->json(['message' => 'Shape file processed and inserted successfully', 'uuid' => $uuid, 'properties' => $properties], 200);
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
        $insertionSuccess = $this->insertCriteriaSite($geometry->id, $SELF_CRITERIA_ID, $isSimple);
        return response()->json(['selfintersects' => $message, 'geometry_id' => $geometry->id, 'insertion_success' => $insertionSuccess, 'valid' => $isSimple ? true : false], 200);

    }
  public function calculateDistance($point1, $point2) {
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
  public function detectSpikes($geometry) {
    $spikes = [];
    if ($geometry['type'] === 'Polygon' || $geometry['type'] === 'MultiPolygon') {
        $coordinates = $geometry['type'] === 'Polygon' ? $geometry['coordinates'][0] : $geometry['coordinates'][0][0]; // First ring of the polygon or the first polygon in the MultiPolygon
        $numVertices = count($coordinates);
        $perimeter = 0;
        for ($i = 0; $i < $numVertices - 1; $i++) {
            $distance = $this->calculateDistance($coordinates[$i], $coordinates[$i + 1]);
            $perimeter += $distance;
        }
        $averageDistance = $perimeter / ($numVertices - 1);
        $threshold = $averageDistance * 1.25; // Adjust this threshold as needed
        for ($i = 0; $i < $numVertices - 1; $i++) {
            $distance = $this->calculateDistance($coordinates[$i], $coordinates[$i + 1]);
            if (abs($distance - $averageDistance) > $threshold) {
                $spikes[] = $coordinates[$i]; 
            }
        }
    }
    return $spikes;
}
public function insertCriteriaSite($POLYGON_ID, $CRITERIA_ID, $valid) {
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

    public function checkBoundarySegments(Request $request) {
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
        $insertionSuccess = $this->insertCriteriaSite($geometry->id, $SPIKE_CRITERIA_ID, $valid);
        return response()->json(['spikes' => $spikes, 'geometry_id' => $geometry->id, 'insertion_success' => $insertionSuccess, 'valid' => $valid], 200);
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
        $insertionSuccess = $this->insertCriteriaSite($geometry->id, $SIZE_CRITERIA_ID, $valid);
        return response()->json([
          'area_hectares' => $areaSqMeters / 10000, // Convert to hectares
          'area_sqmeters' => $areaSqMeters,
          'geometry_id' => $geometry->id,
          'insertion_success' => $insertionSuccess,
          'valid' => $valid
        ], 200);
    }

    public function checkWithinCountry(Request $request) {
      $countryName = $request->input('country');
      $polygonUuid = $request->input('uuid');
      $geometry = PolygonGeometry::where('uuid', $polygonUuid)->first();
    
      if (!$geometry) {
          return response()->json(['error' => 'Geometry not found'], 404);
      }
      $totalArea = DB::table('polygon_geometry')
          ->where('uuid', $polygonUuid)
          ->selectRaw('ST_Area(geom) AS area')
          ->first()->area;
  
      $intersectionArea = DB::table('world_countries_generalized')
          ->where('country', $countryName)
          ->selectRaw('ST_Area(ST_Intersection(world_countries_generalized.geometry, (SELECT geom FROM polygon_geometry WHERE uuid = ?))) AS area', [$polygonUuid])
          ->first()->area;
      $insidePercentage = $intersectionArea / $totalArea * 100;

      $insideThreshold = 75;
      $insideViolation = $insidePercentage < $insideThreshold;
      $WITHIN_COUNTRY_CRITERIA_ID = 7;
      $insertionSuccess = $this->insertCriteriaSite($geometry->id, $WITHIN_COUNTRY_CRITERIA_ID, !$insideViolation);
      return response()->json([
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
            return response()->json(['uuid' => $uuid, 'geometry_type' => $geometryType]);
        } else {
            return response()->json(['error' => 'Geometry not found for the given UUID'], 404);
        }
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
            return response()->json(['error' => 'Failed to insert GeoJSON data into the database'], 500);
          }
          $properties = $this->getGeometryProperties($filename);
          return response()->json(['message' => 'Geojson file processed and inserted successfully', 'uuid' => $uuid, 'properties' => $properties], 200);
      } else {
          return response()->json(['error' => 'GeoJSON file not provided'], 400);
      }
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


}
