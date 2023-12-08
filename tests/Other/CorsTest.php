<?php

namespace Tests\Other;

use Tests\TestCase;

final class CorsTest extends TestCase
{
    public function testCorsRequest(): void
    {
        $headers = [
            'origin' => 'www.example.com',
        ];
        $response = $this->getJson('/api/auth/login', $headers);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
    }
}
