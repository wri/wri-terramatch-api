<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\CarbonCertification;
use App\Models\Pitch;
use Tests\Legacy\LegacyTestCase;

final class CarbonCertificationTest extends LegacyTestCase
{
    public function testCarbonCertificationBelongsToPitch(): void
    {
        $carbonCertification = CarbonCertification::first();

        $this->assertInstanceOf(Pitch::class, $carbonCertification->pitch);
    }
}
