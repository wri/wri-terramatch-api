<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Pitch;
use App\Models\PitchDocument;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class PitchDocumentTest extends LegacyTestCase
{
    #[Test]
    public function testPitchDocumentBelongsToPitch(): void
    {
        $pitchDocument = PitchDocument::first();

        $this->assertInstanceOf(Pitch::class, $pitchDocument->pitch);
    }
}
