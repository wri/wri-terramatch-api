<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\User;
use App\Models\Verification;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class VerificationTest extends LegacyTestCase
{
    #[Test]
    public function testVerificationBelongsToUser(): void
    {
        $verification = Verification::first();

        $this->assertInstanceOf(User::class, $verification->user);
    }
}
