<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\RestorationMethodMetric;
use App\Models\RestorationMethodMetricVersion;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class RestorationMethodMetricVersionTest extends LegacyTestCase
{
    #[Test]
    public function testRestorationMethodMetricVersionBelongsToRestorationMethodMetric(): void
    {
        $restorationMethodMetricVersion = RestorationMethodMetricVersion::first();

        $this->assertInstanceOf(RestorationMethodMetric::class, $restorationMethodMetricVersion->restorationMethodMetric);
    }

    #[Test]
    public function testRestorationMethodMetricVersionBelongsToApprovedRejectedBy(): void
    {
        $restorationMethodMetricVersion = RestorationMethodMetricVersion::first();

        $this->assertInstanceOf(User::class, $restorationMethodMetricVersion->approvedRejectedBy);
    }
}
