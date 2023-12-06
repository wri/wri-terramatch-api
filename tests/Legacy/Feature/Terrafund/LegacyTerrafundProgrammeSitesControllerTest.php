<?php

namespace Tests\Legacy\Feature\Terrafund;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class LegacyTerrafundProgrammeSitesControllerTest extends LegacyTestCase
{
    public function testReadAllProgrammeSites(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programme/1/sites', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
        ]);
    }

    public function testReadAllProgrammeSitesUserMustBeInProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programme/1/sites', $headers)
        ->assertStatus(403);
    }

    public function testCheckHasProgrammeSites(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programme/1/has_sites', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'has_sites' => true,
        ]);
    }

    public function testCheckHasProgrammeSitesUserMustBeInProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programme/1/has_sites', $headers)
        ->assertStatus(403);
    }
}
