<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Draft;
use App\Models\Organisation;
use Tests\Legacy\LegacyTestCase;

final class DraftTest extends LegacyTestCase
{
    public function testDraftBelongsToOrganisation(): void
    {
        $draft = Draft::first();

        $this->assertInstanceOf(Organisation::class, $draft->organisation);
    }
}
