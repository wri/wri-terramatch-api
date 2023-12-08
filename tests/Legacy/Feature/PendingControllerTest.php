<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class PendingControllerTest extends LegacyTestCase
{
    public function testReadPendingProgrammeSubmissionsAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $response = $this->getJson('/api/pending/programme', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'total_reports' => 4,
            'outstanding_reports' => 1,
        ]);
    }

    public function testReadPendingSiteSubmissionsAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $response = $this->getJson('/api/pending/site', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'total_reports' => 6,
            'outstanding_reports' => 2,
        ]);
    }
}
