<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\CarbonCertification;
use App\Models\CarbonCertificationVersion;
use Tests\Legacy\LegacyTestCase;

final class CarbonCertificationVersionTest extends LegacyTestCase
{
    public function testCarbonCertificationVersionBelongsToCarbonCertification(): void
    {
        $carbonCertificationVersion = CarbonCertificationVersion::first();

        $this->assertInstanceOf(CarbonCertification::class, $carbonCertificationVersion->carbonCertification);
    }
}
