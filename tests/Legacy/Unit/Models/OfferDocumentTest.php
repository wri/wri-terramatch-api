<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Offer;
use App\Models\OfferDocument;
use Tests\Legacy\LegacyTestCase;

final class OfferDocumentTest extends LegacyTestCase
{
    public function testOfferDocumentBelongsToOffer(): void
    {
        $offerDocument = OfferDocument::first();

        $this->assertInstanceOf(Offer::class, $offerDocument->offer);
    }
}
