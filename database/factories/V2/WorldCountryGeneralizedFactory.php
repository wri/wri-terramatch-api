<?php

namespace Database\Factories\V2;

use App\Models\V2\WorldCountryGeneralized;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorldCountryGeneralized>
 */
class WorldCountryGeneralizedFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = WorldCountryGeneralized::class;

    public function definition()
    {

        $geometryAustralia = file_get_contents('database/seeders/Data/australia.geojson');

        return [
            'countryaff' => 'Australia',
            'country' => 'Australia',
            'iso' => 'AU',
            'aff_iso' => 'AU',
            'OGR_FID' => 264,
            'geometry' => DB::raw("ST_GeomFromGeoJSON('$geometryAustralia')"),
        ];
    }
}
