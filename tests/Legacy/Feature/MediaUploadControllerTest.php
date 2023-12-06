<?php

namespace Tests\Legacy\Feature;

use Tests\Legacy\LegacyTestCase;

final class MediaUploadControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $data = [
            'is_public' => true,
            'programme_id' => 1,
            'upload' => 1,
        ];

        $response = $this->postJson('/api/uploads/site_programme_media', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
    }

    public function testCreateActionDoesNotRequireMediaTitle(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $data = [
            'is_public' => true,
            'programme_id' => 1,
            'upload' => 1,
        ];

        $response = $this->postJson('/api/uploads/site_programme_media', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
    }
}
