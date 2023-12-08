<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\PasswordReset;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Legacy\LegacyTestCase;

final class PasswordResetTest extends LegacyTestCase
{
    #[Test]
    public function testPasswordResetBelongsToUser(): void
    {
        $passwordReset = PasswordReset::first();

        $this->assertInstanceOf(User::class, $passwordReset->user);
    }
}
