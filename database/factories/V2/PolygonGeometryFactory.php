<?php

namespace Database\Factories\V2;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class PolygonGeometryFactory extends Factory
{
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
        ];
    }

    public function geojson(string|array $geojson)
    {
        $geom = DB::raw("ST_GeomFromGeoJSON('$geojson')");

        return $this->state(function (array $attributes) use ($geom) {
            return [
                'geom' => $geom,
            ];
        });
    }
}
