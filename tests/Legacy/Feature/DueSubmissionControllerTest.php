<?php

namespace Tests\Legacy\Feature;

use Tests\Legacy\LegacyTestCase;

final class DueSubmissionControllerTest extends LegacyTestCase
{
    public function testReadAllDueSiteSubmissionsForUserAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/site/submission/due', $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', 2)
            ->assertJsonPath('data.1.id', 3);
    }

    public function testReadAllDueProgrammeSubmissionsForUserAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/programme/submission/due', $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', 1);
    }
}
