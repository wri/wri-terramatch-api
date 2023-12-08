<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Pitch;
use App\Models\PitchVersion;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class PitchVersionTest extends LegacyTestCase
{
    #[Test]
    public function testPitchVersionBelongsToPitch(): void
    {
        $pitchVersion = PitchVersion::first();

        $this->assertInstanceOf(Pitch::class, $pitchVersion->pitch);
    }

    #[Test]
    public function testPitchVersionBelongsToApprovedRejectedBy(): void
    {
        $pitchVersion = PitchVersion::first();

        $this->assertInstanceOf(User::class, $pitchVersion->approvedRejectedBy);
    }
}
