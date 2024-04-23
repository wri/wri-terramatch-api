<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\V2\WorldCountryGeneralized;

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
}
