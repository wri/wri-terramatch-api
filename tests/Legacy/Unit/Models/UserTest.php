<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Organisation;
use App\Models\User;
use Tests\Legacy\LegacyTestCase;

final class UserTest extends LegacyTestCase
{
    public function testUserBelongsToOrganisation(): void
    {
        $user = User::where('organisation_id', 2)->first();

        $this->assertInstanceOf(Organisation::class, $user->organisation);
    }
}
