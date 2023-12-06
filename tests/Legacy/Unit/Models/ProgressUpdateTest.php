<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Monitoring;
use App\Models\ProgressUpdate;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class ProgressUpdateTest extends LegacyTestCase
{
    #[Test]
    public function testProgressUpdateBelongsToMonitoring(): void
    {
        $progressUpdate = ProgressUpdate::first();

        $this->assertInstanceOf(Monitoring::class, $progressUpdate->monitoring);
    }

    #[Test]
    public function testProgressUpdateBelongsToUser(): void
    {
        $progressUpdate = ProgressUpdate::first();

        $this->assertInstanceOf(User::class, $progressUpdate->createdBy);
    }
}
