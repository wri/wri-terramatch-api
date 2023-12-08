<?php


namespace Tests\Legacy\Unit\Models;

use App\Models\Interest;
use App\Models\Matched;
use Tests\Legacy\LegacyTestCase;

final class MatchTest extends LegacyTestCase
{
    public function testMatchBelongsToPrimaryInterest(): void
    {
        $matched = Matched::first();

        $this->assertInstanceOf(Interest::class, $matched->interest);
    }

    public function testMatchBelongsToSecondaryInterest(): void
    {
        $matched = Matched::first();

        $this->assertInstanceOf(Interest::class, $matched->secondaryInterest);
    }
}
