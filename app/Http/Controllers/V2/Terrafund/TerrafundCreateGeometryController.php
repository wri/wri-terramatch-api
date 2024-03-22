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
        // Validate incoming request if needed
        $validatedData = $request->validate([
            'geometry' => 'required|json',
        ]);

        // Process the incoming JSON payload
        $geometry = json_decode($request->input('geometry'));

        Log::info('Geometry received: ' . json_encode($geometry));

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
        // Read GeoJSON file contents
        $geojsonData = Storage::get("public/geojson_files/{$geojsonFilename}");
        $geojson = json_decode($geojsonData, true);
    
        // Check if GeoJSON contains features
        if (!isset($geojson['features'])) {
            // If no features, return with an error message
            return ['error' => 'GeoJSON file does not contain features'];
        }
    
        // Iterate over each feature
        $uuids = [];
        foreach ($geojson['features'] as $feature) {
            // Check if feature is of type "Polygon" or "MultiPolygon"
            if ($feature['geometry']['type'] === 'Polygon') {
                // If single polygon, insert into the database
                $uuids[] = $this->insertSinglePolygon($feature['geometry']);
            } elseif ($feature['geometry']['type'] === 'MultiPolygon') {
                // If multi-polygon, iterate over each polygon and insert into the database
                foreach ($feature['geometry']['coordinates'] as $polygon) {
                    $uuids[] = $this->insertSinglePolygon(['type' => 'Polygon', 'coordinates' => $polygon]);
                }
            }
        }
    
        return $uuids;
    }
    public function uploadKMLFile(Request $request) {
      if ($request->hasFile('kml_file')) {
          $kmlfile = $request->file('kml_file');
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
          // Return a success response
          return response()->json(['message' => 'KML file processed and inserted successfully', 'uuid' => $uuid], 200);
      
      } else {
          return response()->json(['error' => 'KML file not provided'], 400);
      }
  }

public function uploadShapefile(Request $request) {
    if ($request->hasFile('shapefile')) {
        $file = $request->file('shapefile');
        if ($file->getClientOriginalExtension() !== 'zip') {
            return response()->json(['error' => 'Only ZIP files are allowed'], 400);
        }
        $directory = storage_path('app/public/shapefiles/' . uniqid('shapefile_'));
        mkdir($directory, 0755, true);
        $zip = new \ZipArchive();
        if ($zip->open($file->getPathname()) === true) {
            $zip->extractTo($directory);
            $zip->close();
            return response()->json(['message' => 'Shapefile extracted and uploaded successfully', 'directory' => $directory, ''], 200);
        } else {
            
            return response()->json(['error' => 'Failed to open the ZIP file'], 400);
        }
    } else {
        
        return response()->json(['error' => 'No file uploaded'], 400);
    }
}

  
    public function uploadGeoJSONFile(Request $request)
    { 
      $validatedData = $request->validate([
          'geojson_file' => 'required|file|mimetypes:application/json', 
      ]); 
      $file = $request->file('geojson_file');
      $filename = $file->getClientOriginalName();
      $directory = storage_path('app/public/geojson');
      if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
      }
      $file->move($directory, $filename);
      return response()->json(['message' => 'Geometry received successfully', $filename], 200);
    }
}
