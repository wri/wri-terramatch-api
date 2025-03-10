<?php

namespace Tests\Unit\Validators\Extensions;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\WorldCountryGeneralized;
use App\Services\PolygonService;
use App\Validators\SitePolygonValidator;
use Database\Seeders\PolygonValidationSeeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Tests\TestCase;

class PolygonValidatorsTest extends TestCase
{
    public const FILES_DIR = 'tests/Unit/Validators/Extensions/Polygons/TestFiles/';

    public function test_coordinate_system()
    {
        $this->runValidationTest('FEATURE_BOUNDS');
    }

    public function test_not_overlapping()
    {
        $this->runValidationImportTest('NOT_OVERLAPPING');
    }

    public function test_self_intersection()
    {
        $this->runValidationTest('SELF_INTERSECTION');
    }

    public function test_size_limit()
    {
        $this->runValidationTest('POLYGON_SIZE');
    }

    public function test_within_country()
    {
        $this->runValidationImportTest('WITHIN_COUNTRY');
    }

    public function test_detect_spikes()
    {
        $this->runValidationTest('SPIKES');
    }

    public function test_estimated_area()
    {
        $this->runValidationImportTest('ESTIMATED_AREA');
    }

    public function test_geometry_type()
    {
        $this->runValidationTest('GEOMETRY_TYPE');
        $mixedGeojsonPath = self::FILES_DIR . 'geometry_type_mixed.geojson';
        $mixedGeojson = json_decode(file_get_contents($mixedGeojsonPath), true);
        $this->assertFalse(
            SitePolygonValidator::isValid('GEOMETRY_TYPE', $mixedGeojson, false)
        );
    }

    public function test_schema()
    {
        $this->runValidationTest('SCHEMA');
    }

    public function test_data()
    {
        $this->runValidationTest('DATA');
    }

    protected function runValidationTest(string $validationName): void
    {
        $this->readGeojsons($validationName, function ($passGeojson, $failGeojson) use ($validationName) {
            $this->assertTrue(SitePolygonValidator::isValid($validationName, $passGeojson, false));
            $this->assertFalse(SitePolygonValidator::isValid($validationName, $failGeojson, false));
        });
    }

    protected function runValidationImportTest(string $validationName): void
    {
        $this->seed(PolygonValidationSeeder::class);
        if (WorldCountryGeneralized::count() == 0) {
            WorldCountryGeneralized::factory()->create();
        }

        $this->readGeojsons($validationName, function ($passGeojson, $failGeojson) use ($validationName) {
            /** @var PolygonService $service */
            $service = App::make(PolygonService::class);
            $passUuids = $service->createGeojsonModels($passGeojson);
            $this->assertTrue(SitePolygonValidator::isValid($validationName, $passUuids, false));

            SitePolygon::whereIn('poly_id', $passUuids)->delete();
            PolygonGeometry::whereIn('uuid', $passUuids)->delete();
            $failUuids = $service->createGeojsonModels($failGeojson);
            $this->assertFalse(SitePolygonValidator::isValid($validationName, $failUuids, false));
        });
    }

    protected function readGeojsons(string $validationName, callable $callback): void
    {
        $passFile = self::FILES_DIR . Str::lower($validationName) . '_pass.geojson';
        $passGeojson = json_decode(file_get_contents($passFile), true);
        $failFile = self::FILES_DIR . Str::lower($validationName) . '_fail.geojson';
        $failGeojson = json_decode(file_get_contents($failFile), true);
        $callback($passGeojson, $failGeojson);
    }
}
