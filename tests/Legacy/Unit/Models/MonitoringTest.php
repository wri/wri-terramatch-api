<?php


namespace Tests\Legacy\Unit\Models;

use App\Models\Matched;
use App\Models\Monitoring;
use App\Models\User;
use Tests\Legacy\LegacyTestCase;

final class MonitoringTest extends LegacyTestCase
{
    public function testMonitoringBelongsToMatch(): void
    {
        $monitoring = Monitoring::first();

        $this->assertInstanceOf(Matched::class, $monitoring->matched);
    }

    public function testMonitoringBelongsToUser(): void
    {
        $monitoring = Monitoring::first();

        $this->assertInstanceOf(User::class, $monitoring->createdBy);
    }
}
