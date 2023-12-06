<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\Upload;
use App\Models\User;
use Tests\Legacy\LegacyTestCase;

final class UploadTest extends LegacyTestCase
{
    public function testUploadBelongsToUser(): void
    {
        $upload = Upload::first();

        $this->assertInstanceOf(User::class, $upload->user);
    }
}
