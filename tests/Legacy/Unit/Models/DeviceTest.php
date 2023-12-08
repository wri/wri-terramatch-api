<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Device;
use App\Models\User;
use Tests\Legacy\LegacyTestCase;

final class DeviceTest extends LegacyTestCase
{
    public function testDeviceBelongsToUser(): void
    {
        $device = Device::first();

        $this->assertInstanceOf(User::class, $device->user);
    }
}
