<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\PitchDocument;
use App\Models\PitchDocumentVersion;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class PitchDocumentVersionTest extends LegacyTestCase
{
    #[Test]
    public function testPitchDocumentVersionBelongsToPitchDocument(): void
    {
        $pitchDocumentVersion = PitchDocumentVersion::first();

        $this->assertInstanceOf(PitchDocument::class, $pitchDocumentVersion->pitchDocument);
    }

    #[Test]
    public function testPitchDocumentVersionBelongsToApprovedRejectedBy(): void
    {
        $pitchDocumentVersion = PitchDocumentVersion::first();

        $this->assertInstanceOf(User::class, $pitchDocumentVersion->approvedRejectedBy);
    }
}
