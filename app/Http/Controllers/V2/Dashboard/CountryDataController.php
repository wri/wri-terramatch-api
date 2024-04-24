<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\WorldCountryGeneralized;
use Illuminate\Support\Facades\Log;

class CountryDataController extends Controller
{
    public function getCountryBbox(string $iso)
    {
        // Get the bbox of the country and the name
        $countryData = WorldCountryGeneralized::where('iso', $iso)
            ->selectRaw('ST_AsGeoJSON(ST_Envelope(geometry)) AS bbox, country')
            ->first();

        if (! $countryData) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        // Decode the GeoJSON bbox
        $geoJson = json_decode($countryData->bbox);

        // Extract the bounding box coordinates
        $coordinates = $geoJson->coordinates[0];

        // Get the country name
        $countryName = $countryData->country;

        // Construct the bbox data in the specified format
        $countryBbox = [
            $countryName,
            [$coordinates[0][0], $coordinates[0][1], $coordinates[2][0], $coordinates[2][1]],
        ];

        return response()->json(['bbox' => $countryBbox]);
    }
    public function getPolygonData(string $uuid) 
    {
        $sitePolygon = SitePolygon::where('poly_id', $uuid)->first();
    
        if (! $sitePolygon) {
            return response()->json(['error' => 'Polygon not found'], 404);
        }
    
        $project = $sitePolygon->project()->first();
    
        if (! $project) {
          Log::error("Project not found for site polygon with ID: $sitePolygon->id");
        }
    
        $site = $sitePolygon->site()->first();
    
        if(! $site) {
          Log::error("Site not found for site polygon with ID: $sitePolygon->id");
        }
        
        $organization = $project->organisation()->first();

        if (! $organization) {
          Log::error("Organization not found for project with ID: $project->id");
        }

        $country = WorldCountryGeneralized::where('iso', $project->country)->first();
        $data = [
            ['title' => 'Polygon Name', 'value' => $sitePolygon->poly_name],
            ['title' => 'Site', 'value' => $site->name],
            ['title' => 'Number of trees', 'value' => $sitePolygon->num_trees],
            ['title' => 'Country', 'value' => $country->country],
            ['title' => 'country_iso', 'value' => $project->country],
            ['title' => 'Project', 'value' => $project->name],
            ['title' => 'Site', 'value' => $site->name],
            ['title' => 'Organization', 'value' => $organization->name],
            ['title' => 'Plant Start Date', 'value' => $sitePolygon->plantstart],
            ['title' => 'Status', 'value' => $sitePolygon->status]
        ];
    
        return response()->json(['data' => $data]);
    }
    

}
