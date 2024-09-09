<?php

namespace Tests\V2\Geometry;

use App\Models\V2\Sites\Site;
use App\Models\V2\User;
use App\Models\V2\WorldCountryGeneralized;
use App\Services\PythonService;
use Database\Seeders\WorldCountriesGeneralizedTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;

class GeometryControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_geometry_payload_validation()
    {
        $service = User::factory()->serviceAccount()->create();
        if (WorldCountryGeneralized::count() == 0) {
            $this->seed(WorldCountriesGeneralizedTableSeeder::class);
        }

        // No geometry
        $this->assertCreateError('features field is required', $service, [
            $this->fakeGeojson([]),
        ]);

        // Invalid geometry type
        $this->assertCreateError('type is invalid', $service, [
            $this->fakeGeojson([['type' => 'Feature', 'geometry' => ['type' => 'MultiPolygon']]]),
        ]);

        // Multiple polygons
        $this->assertCreateError('features must contain 1 item', $service, [
            $this->fakeGeojson([$this->fakePolygon(), $this->fakePolygon()]),
        ]);

        // Missing site id
        $this->assertCreateError('site id field is required', $service, [
            $this->fakeGeojson([$this->fakePolygon()]),
        ]);

        // Mixing polygons and points
        $this->assertCreateError('type is invalid', $service, [
            $this->fakeGeojson([$this->fakePoint(), $this->fakePolygon()]),
        ]);

        // Multiple site ids
        $this->assertCreateError('site ids must contain 1 item', $service, [
            $this->fakeGeojson([$this->fakePoint(['site_id' => '123']), $this->fakePoint(['site_id' => '456'])]),
        ]);

        // Missing est area
        $this->assertCreateError('est_area field is required', $service, [
            $this->fakeGeojson([$this->fakePoint(['site_id' => '123'])]),
        ]);

        // Invalid est area
        $this->assertCreateError('est_area must be at least 0.0001', $service, [
            $this->fakeGeojson([$this->fakePoint(['site_id' => '123', 'est_area' => -1])]),
        ]);

        // Not all sites found
        $site = Site::factory()->create();
        $this->assertCreateError('num sites and num site ids must match', $service, [
            $this->fakeGeojson([$this->fakePolygon(['site_id' => $site->uuid])]),
            $this->fakeGeojson([$this->fakePolygon(['site_id' => 'asdf'])]),
        ]);

        // Valid payload
        $this->mock(PythonService::class, function (MockInterface $mock) use ($site) {
            $mock
                ->shouldReceive('voronoiTransformation')
                ->andReturn($this->fakeGeojson([
                    $this->fakePolygon(['site_id' => $site->uuid]),
                    $this->fakePolygon(['site_id' => $site->uuid]),
                ]))
                ->once();
        });
        $this->actingAs($service)
            ->postJson('/api/v2/geometry', ['geometries' => [
                $this->fakeGeojson([$this->fakePolygon(['site_id' => $site->uuid])]),
                $this->fakeGeojson([
                    $this->fakePoint(['site_id' => $site->uuid, 'est_area' => 4]),
                    $this->fakePoint(['site_id' => $site->uuid, 'est_area' => 3]),
                ]),
            ]])
            ->assertStatus(201);
    }

    protected function assertCreateError(string $expected, $user, $geometries): void
    {
        $content = $this
            ->actingAs($user)
            ->postJson('/api/v2/geometry', ['geometries' => $geometries])
            ->assertStatus(422)
            ->json();
        $this->assertStringContainsString($expected, implode('|', data_get($content, 'errors.*.detail')));
    }

    protected function fakeGeojson($features): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    protected function fakePoint($properties = []): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [45, -120],
            ],
            'properties' => $properties,
        ];
    }

    protected function fakePolygon($properties = []): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[
                    [
                        40.405701461490054,
                        -12.96724571876176,
                    ],
                    [
                        40.40517180334834,
                        -12.965903759897898,
                    ],
                    [
                        40.405701461490054,
                        -12.96724571876176,
                    ],
                ]],
            ],
            'properties' => $properties,
        ];
    }
}
