<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Organisation;
use App\Models\Pitch;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class PitchTest extends LegacyTestCase
{
    #[Test]
    public function testPitchBelongsToOrganisation(): void
    {
        $pitch = Pitch::first();

        $this->assertInstanceOf(Organisation::class, $pitch->organisation);
    }
}
