<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class LandTenureControllerTest extends LegacyTestCase
{
    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/site/land_tenures', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
            'name' => 'Public',
            'key' => 'public',
        ])
        ->assertJsonFragment([
            'id' => 2,
            'name' => 'Private',
            'key' => 'private',
        ]);
    }
}
