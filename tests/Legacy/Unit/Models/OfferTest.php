<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Offer;
use App\Models\Organisation;
use Tests\Legacy\LegacyTestCase;

final class OfferTest extends LegacyTestCase
{
    public function testOfferBelongsToOrganisation(): void
    {
        $offer = Offer::first();

        $this->assertInstanceOf(Organisation::class, $offer->organisation);
    }
}
