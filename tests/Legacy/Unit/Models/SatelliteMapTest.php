<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Monitoring;
use App\Models\SatelliteMap;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class SatelliteMapTest extends LegacyTestCase
{
    #[Test]
    public function testSatelliteMapBelongsToMonitoring(): void
    {
        $satelliteMap = SatelliteMap::first();

        $this->assertInstanceOf(Monitoring::class, $satelliteMap->monitoring);
    }

    #[Test]
    public function testSatelliteMapBelongsToUser(): void
    {
        $satelliteMap = SatelliteMap::first();

        $this->assertInstanceOf(User::class, $satelliteMap->createdBy);
    }
}
