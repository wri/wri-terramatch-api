<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::dropIfExists('polygon_geometry');
      Schema::create('polygon_geometry', function (Blueprint $table) {
          $table->id();
          $table->uuid('uuid')->unique();
          $table->integer('polygon_id')->nullable();;
          $table->geometry('geom')->nullable();;
          // $table->foreign('polygon_id')->references('id')->on('indicator_site')->onDelete('cascade');
          $table->softDeletes();
          $table->timestamps();
      });
    //   $sites = DB::table('v2_sites')
    //   ->whereNotNull('boundary_geojson')
    //   ->where('boundary_geojson', '!=', 'null')
    //   ->where('boundary_geojson', '!=', '')
    //   ->get();

    //   foreach ($sites as $site) {
    //     $geojson = json_decode($site->boundary_geojson);
    
    //     if ($geojson && isset($geojson->type) && isset($geojson->features) && is_array($geojson->features) && count($geojson->features) > 0) {
    //         $geometry = $geojson->features[0]->geometry;
    //         // Only polygons
    //         if ($geometry && isset($geometry->type) && $geometry->type === "Polygon") {
    //             // Convert the extracted geometry to a format that can be inserted into the database
    //             $geom = DB::raw("ST_GeomFromGeoJSON('" . json_encode($geometry) . "')");
    
    //             // Insert the geometry into the database
    //             DB::table('polygon_geometry')->insert([
    //                 'uuid' => Str::uuid(),
    //                 'geom' => $geom,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         } else {
    //             // Handle the case where the geometry is null or not of type "Polygon"
    //             echo "Invalid or unsupported geometry for record with ID: " . $site->id . "<br>";
    //         }
    //     } else {
    //         // Handle the case where the GeoJSON object is invalid or does not contain the expected properties
    //         echo "Invalid GeoJSON data for record with ID: " . $site->id . "<br>";
    //     }
    //   }
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polygon_geometry');
    }
};
