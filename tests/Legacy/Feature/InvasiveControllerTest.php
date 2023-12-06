<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class InvasiveControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'name' => 'test name',
            'type' => 'common',
        ];

        $this->postJson('/api/site/1/invasives', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'test name',
                'type' => 'common',
                'site_id' => 1,
            ]);
    }
}
