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
    public function uploadKMLFile(Request $request) {
      
      if ($request->hasFile('kml_file')) {
          $file = $request->file('kml_file');
  
          $directory = storage_path('app/public/kml_files');
  
          if (!file_exists($directory)) {
              mkdir($directory, 0755, true);
          }
  
          $filename = uniqid('kml_file_') . '.' . $file->getClientOriginalExtension();
  
          $file->move($directory, $filename);
  
          return response()->json(['message' => 'KML file uploaded successfully', 'filename' => $filename], 200);
      } else {
          return response()->json(['error' => 'KML file not provided'], 400);
      }
  }
  
    public function uploadShapefile(Request $request) {
      $hasFile = $request->hasFile('shapefile');
      if ($hasFile) {
          $file = $request->file('shapefile');
          $directory = storage_path('app/public/shapefiles');
          if (!file_exists($directory)) {
              mkdir($directory, 0755, true);
          }
          $filename = uniqid('shapefile_') . '.' . $file->getClientOriginalExtension();

          $file->move($directory, $filename);
          return response()->json(['message' => 'Shapefile uploaded successfully', 'filename' => $filename], 200);
      } else {
          return response()->json(['error' => $hasFile], 400);
      }
    }
  
    public function uploadGeoJSONFile(Request $request)
    {
      // Validate incoming request
      $validatedData = $request->validate([
          'geojson_file' => 'required|file|mimetypes:application/json', // Validate GeoJSON file
      ]);
      
      $file = $request->file('geojson_file');
      $filename = $file->getClientOriginalName();
      $directory = storage_path('app/public/geojson');
      if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
      }
      // $destination = $directory . '/' . $filename;
      // $writerType = 'geojson';
      // $name = 'exports/all-entity-records/'.$filename;
      // Excel::store($file, $name, 's3', $writerType);
      $file->move($directory, $filename);
      return response()->json(['message' => 'Geometry received successfully', $filename], 200);
    }
}
