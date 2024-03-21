<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TerrafundCreateGeometryController extends Controller
{
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

        // Log::info($geom);

        DB::table('polygon_geometry')->insert([
            'uuid' => Str::uuid(),
            'geom' => $geom,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Return a response
        return response()->json(['message' => 'Geometry received successfully'], 200);
    }
   
    public function uploadGeoJSONFile(Request $request)
    {
      // Validate incoming request
      $validatedData = $request->validate([
          'geojson_file' => 'required|file|mimetypes:application/json', // Validate GeoJSON file
      ]);
      
      $file = $request->file('geojson_file');
      $filename = $file->getClientOriginalName();
      // $directory = storage_path('app/public/geojson');
      // if (!file_exists($directory)) {
      //   mkdir($directory, 0755, true);
      // }
      // $destination = $directory . '/' . $filename;
      $writerType = 'geojson';
      $name = 'exports/all-entity-records/'.$filename;
      Excel::store($file, $name, 's3', $writerType);
      // $file->move($directory, $filename);
      return response()->json(['message' => 'Geometry received successfully', $name], 200);
    }
}
