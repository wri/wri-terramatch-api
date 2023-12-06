<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\PitchContact;
use App\Models\TeamMember;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class PitchContactTest extends LegacyTestCase
{
    #[Test]
    public function testPitchContactBelongsToUser(): void
    {
        $pitchContact = PitchContact::first();

        $this->assertInstanceOf(User::class, $pitchContact->user);
    }

    #[Test]
    public function testPitchContactBelongsToTeamMember(): void
    {
        $pitchContact = PitchContact::where('id', 3)->first();

        $this->assertInstanceOf(TeamMember::class, $pitchContact->team_member);
    }
}
