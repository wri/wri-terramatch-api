<?php

namespace Database\Factories\V2\Sites;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class SitePolygonFactory extends Factory
{
  public function definition()
  {
      $geojson = [
          'type' => 'Polygon',
          'coordinates' => [
              [
                  [0, 0],
                  [1, 0],
                  [1, 1],
                  [0, 1],
                  [0, 0]
              ]
          ]
      ];
  
      return [
          'poly_id' => PolygonGeometry::factory()
              ->geojson($geojson)
              ->create()
              ->uuid,
          'site_id' => Site::factory()->create()->uuid,
          'calc_area' => $this->faker->numberBetween(2.0, 50.0),
      ];
  }
    public function site(Site $site)
    {
        return $this->state(function (array $attributes) use ($site) {
            return [
                'site_id' => $site->uuid,
            ];
        });
    }

    public function geometry(PolygonGeometry $geometry)
    {
        return $this->state(function (array $attributes) use ($geometry) {
            return [
                'poly_id' => $geometry->uuid,
            ];
        });
    }
}
