<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LandscapeGeomTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('landscape_geom')->delete();

        $geojson = json_decode(file_get_contents(database_path('seeders/Landscapes_polygons.geojson')), true);

        foreach ($geojson['features'] as $feature) {
            $geometry = json_encode($feature['geometry']);
            $landscape = $feature['properties']['landscape'];

            DB::table('landscape_geom')->insert([
                'geometry' => DB::raw("ST_GeomFromGeoJSON('$geometry')"),
                'landscape' => $landscape,
            ]);
        }
    }
}
