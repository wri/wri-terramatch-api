<?php

namespace Tests\Legacy\Feature;

use Tests\Legacy\LegacyTestCase;

final class DefaultControllerTest extends LegacyTestCase
{
    public function testIndexAction(): void
    {
        $response = $this->getJson('/');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJson([
            'data' => [],
        ]);
    }
}
