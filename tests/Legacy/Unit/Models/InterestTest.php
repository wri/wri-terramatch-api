<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Interest;
use App\Models\Offer;
use App\Models\Organisation;
use App\Models\Pitch;
use Tests\Legacy\LegacyTestCase;

final class InterestTest extends LegacyTestCase
{
    public function testInterestBelongsToOffer(): void
    {
        $interest = Interest::first();

        $this->assertInstanceOf(Offer::class, $interest->offer);
    }

    public function testInterestBelongsToPitch(): void
    {
        $interest = Interest::first();

        $this->assertInstanceOf(Pitch::class, $interest->pitch);
    }

    public function testInterestBelongsToOrganisation(): void
    {
        $interest = Interest::first();

        $this->assertInstanceOf(Organisation::class, $interest->organisation);
    }
}
