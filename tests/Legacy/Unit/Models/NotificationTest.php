<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Notification;
use App\Models\User;
use Tests\Legacy\LegacyTestCase;

final class NotificationTest extends LegacyTestCase
{
    public function testNotificationBelongsToUser(): void
    {
        $notification = Notification::first();

        $this->assertInstanceOf(User::class, $notification->user);
    }
}
