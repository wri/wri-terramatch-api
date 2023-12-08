<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\ElevatorVideo;
use App\Models\Upload;
use App\Models\User;
use Tests\Legacy\LegacyTestCase;

final class ElevatorVideoTest extends LegacyTestCase
{
    public function testElevatorVideoBelongsToUpload(): void
    {
        $elevatorVideo = ElevatorVideo::first();

        $this->assertInstanceOf(Upload::class, $elevatorVideo->upload);
    }

    public function testElevatorVideoBelongsToUser(): void
    {
        $elevatorVideo = ElevatorVideo::first();

        $this->assertInstanceOf(User::class, $elevatorVideo->user);
    }
}
