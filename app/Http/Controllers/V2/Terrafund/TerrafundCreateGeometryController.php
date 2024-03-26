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
    private function insertSinglePolygon(array $geometry)
    {
        // Convert geometry to GeoJSON string
        $geojson = json_encode(['type' => 'Feature', 'geometry' => $geometry]);

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
        $geojsonData = Storage::get("public/geojson_files/{$geojsonFilename}");
        $geojson = json_decode($geojsonData, true);
        if (!isset($geojson['features'])) {
            return ['error' => 'GeoJSON file does not contain features'];
        }
        $uuids = [];
        foreach ($geojson['features'] as $feature) {
            if ($feature['geometry']['type'] === 'Polygon') {
                $uuids[] = $this->insertSinglePolygon($feature['geometry']);
            } elseif ($feature['geometry']['type'] === 'MultiPolygon') {
                foreach ($feature['geometry']['coordinates'] as $polygon) {
                    $uuids[] = $this->insertSinglePolygon(['type' => 'Polygon', 'coordinates' => $polygon]);
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

        // Fetch polygons from the database as GeoJSON
        $polygons = DB::table('polygon_geometry')
            ->select(DB::raw('ST_AsGeoJSON(geom) AS geojson'))
            ->orderBy('created_at', 'desc')
            ->whereNotNull('geom')
            ->limit($limit)
            ->get();

        // Array to store GeoJSON features
        $features = [];

        foreach ($polygons as $polygon) {
            $coordinates = json_decode($polygon->geojson)->coordinates;
            $feature = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => $coordinates,
                ],
                'properties' => [], // You can add properties if needed
            ];
            $features[] = $feature;
        }

        // Convert features array to GeoJSON format
        $geojson = [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];

        // Return the GeoJSON data
        return response()->json($geojson);
    }


}
