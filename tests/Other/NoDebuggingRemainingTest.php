<?php

namespace Tests\Other;

use Tests\TestCase;

class NoDebuggingRemainingTest extends TestCase
{
    public function testAllFilesAreFreeFromDebugging()
    {
        $dds = $this->searchCodebase(" dd(", __DIR__ . "/../../app");
        $this->assertCount(0, $dds);
        $varDumps = $this->searchCodebase(" var_dump(", __DIR__ . "/../../app");
        $this->assertCount(0, $varDumps);
        $dumps = $this->searchCodebase(" dump(", __DIR__ . "/../../app");
        $this->assertCount(0, $dumps);
    }
}
