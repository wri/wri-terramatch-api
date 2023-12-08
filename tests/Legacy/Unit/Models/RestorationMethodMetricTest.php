<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Pitch;
use App\Models\RestorationMethodMetric;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class RestorationMethodMetricTest extends LegacyTestCase
{
    #[Test]
    public function testRestorationMethodMetricBelongsToPitch(): void
    {
        $restorationMethodMetric = RestorationMethodMetric::first();

        $this->assertInstanceOf(Pitch::class, $restorationMethodMetric->pitch);
    }
}
