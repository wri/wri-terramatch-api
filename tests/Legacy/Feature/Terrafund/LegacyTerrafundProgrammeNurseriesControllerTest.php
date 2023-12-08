<?php

namespace Tests\Legacy\Feature\Terrafund;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class LegacyTerrafundProgrammeNurseriesControllerTest extends LegacyTestCase
{
    public function testReadAllProgrammeNurseriesUserMustBeInProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programme/1/nurseries', $headers)
        ->assertStatus(403);
    }

    public function testCheckHasProgrammeNurseries(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programme/1/has_nurseries', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'has_nurseries' => true,
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

        $this->getJson('/api/terrafund/programme/1/has_nurseries', $headers)
        ->assertStatus(403);
    }
}
