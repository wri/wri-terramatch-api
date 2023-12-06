<?php

namespace Tests\Legacy\Other;

use Tests\Legacy\LegacyTestCase;

final class NoDebuggingRemainingTest extends LegacyTestCase
{
    public function testAllFilesAreFreeFromDebugging(): void
    {
        $dds = $this->searchCodebase(' dd(', __DIR__ . '/../../../app');
        $this->assertCount(0, $dds);
        $varDumps = $this->searchCodebase(' var_dump(', __DIR__ . '/../../../app');
        $this->assertCount(0, $varDumps);
        $dumps = $this->searchCodebase(' dump(', __DIR__ . '/../../../app');
        $this->assertCount(0, $dumps);
    }
}
