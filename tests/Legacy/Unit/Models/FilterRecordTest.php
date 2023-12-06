<?php

namespace Tests\Legacy\Unit\Models;

use App\Models\FilterRecord;
use App\Models\Organisation;
use App\Models\User;
use Tests\Legacy\LegacyTestCase;

final class FilterRecordTest extends LegacyTestCase
{
    public function testFilterRecordBelongsToUser(): void
    {
        $filterRecord = FilterRecord::first();

        $this->assertInstanceOf(User::class, $filterRecord->user);
    }

    public function testFilterRecordBelongsToOrganisation(): void
    {
        $filterRecord = FilterRecord::first();

        $this->assertInstanceOf(Organisation::class, $filterRecord->organisation);
    }
}
