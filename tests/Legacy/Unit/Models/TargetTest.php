<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Monitoring;
use App\Models\Target;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class TargetTest extends LegacyTestCase
{
    #[Test]
    public function testTargetBelongsToMonitoring(): void
    {
        $target = Target::first();

        $this->assertInstanceOf(Monitoring::class, $target->monitoring);
    }

    #[Test]
    public function testTargetBelongsToUser(): void
    {
        $target = Target::first();

        $this->assertInstanceOf(User::class, $target->createdBy);
    }

    #[Test]
    public function testTargetBelongsToAcceptedByUser(): void
    {
        $target = Target::first();

        $this->assertInstanceOf(User::class, $target->acceptedBy);
    }
}
