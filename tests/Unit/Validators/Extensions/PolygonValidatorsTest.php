<?php

namespace Tests\Unit\Validators\Extensions;

use App\Validators\SitePolygonValidator;
use Illuminate\Support\Str;
use Tests\TestCase;

class PolygonValidatorsTest extends TestCase
{
    const FILES_DIR = "tests/Unit/Validators/Extensions/Polygons/TestFiles/";

    public function test_coordinate_system()
    {
        $this->runValidationTest('FEATURE_BOUNDS');
    }

    public function test_self_intersection()
    {
        $this->runValidationTest('SELF_INTERSECTION');
    }

    public function test_size_limit()
    {
        $this->runValidationTest('POLYGON_SIZE');
    }

    public function test_detect_spikes()
    {
        $this->runValidationTest('SPIKES');
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
        $passFile = self::FILES_DIR . Str::lower($validationName) . '_pass.geojson';
        $passGeojson = json_decode(file_get_contents($passFile), true);
        $failFile = self::FILES_DIR . Str::lower($validationName) . '_fail.geojson';
        $failGeojson = json_decode(file_get_contents($failFile), true);

        $this->assertTrue(SitePolygonValidator::isValid($validationName, $passGeojson, false));
        $this->assertFalse(SitePolygonValidator::isValid($validationName, $failGeojson, false));
    }
}
