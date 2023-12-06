<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class PitchContactsControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $this->callCreateActionWithUser();
        $this->callCreateActionWithTeamMember();
    }

    private function callCreateActionWithUser()
    {
        $data = [
            'pitch_id' => 1,
            'user_id' => 6,
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/pitch_contacts', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'pitch_id',
                'first_name',
                'last_name',
            ],
            'meta' => [],
        ]);
    }

    private function callCreateActionWithTeamMember()
    {
        $data = [
            'pitch_id' => 1,
            'team_member_id' => 1,
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/pitch_contacts', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'team_member_id',
                'pitch_id',
                'first_name',
                'last_name',
            ],
            'meta' => [],
        ]);
    }

    public function testReadAllByPitchAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/pitches/1/pitch_contacts', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'pitch_id',
                    'first_name',
                    'last_name',
                ],
            ],
        ]);
    }

    public function testDeleteAction(): void
    {
        $data = [
            'pitch_id' => 1,
            'user_id' => 6,
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/pitch_contacts', $data, $headers);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->deleteJson('/api/pitch_contacts/3', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson(['data' => []]);
    }
}
