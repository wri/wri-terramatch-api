<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Organisation;
use App\Models\TeamMember;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class TeamMemberTest extends LegacyTestCase
{
    #[Test]
    public function testTeamMemberBelongsToOrganisation(): void
    {
        $teamMember = TeamMember::first();

        $this->assertInstanceOf(Organisation::class, $teamMember->organisation);
    }
}
