<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Offer;
use App\Models\OfferContact;
use App\Models\TeamMember;
use App\Models\User;
use Tests\Legacy\LegacyTestCase;

final class OfferContactTest extends LegacyTestCase
{
    public function testOfferContactBelongsToTeamMember(): void
    {
        $offerContact = OfferContact::whereNotNull('team_member_id')->first();

        $this->assertInstanceOf(TeamMember::class, $offerContact->team_member);
    }

    public function testOfferContactBelongsToUser(): void
    {
        $offerContact = OfferContact::whereNotNull('user_id')->first();

        $this->assertInstanceOf(User::class, $offerContact->user);
    }

    public function testOfferContactBelongsToOffer(): void
    {
        $offerContact = OfferContact::first();

        $this->assertInstanceOf(Offer::class, $offerContact->offer);
    }
}
