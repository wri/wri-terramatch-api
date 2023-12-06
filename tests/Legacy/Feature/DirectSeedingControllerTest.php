<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class DirectSeedingControllerTest extends LegacyTestCase
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
            'name' => 'seed name',
            'weight' => 123,
        ];

        $this->postJson('/api/site/submission/1/direct_seeding', $data, $headers)
        ->assertHeader('Content-Type', 'application/json')
        ->assertStatus(201)
        ->assertJsonFragment([
            'name' => 'seed name',
            'weight' => 123,
            'site_submission_id' => 1,
        ]);
    }

    public function testDeleteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->deleteJson('/api/site/submission/direct_seeding/1', $headers)
        ->assertHeader('Content-Type', 'application/json')
        ->assertStatus(200);
    }
}
