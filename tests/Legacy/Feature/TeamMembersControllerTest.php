<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class TeamMembersControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $data = [
            'first_name' => 'Oliver',
            'last_name' => 'Smith',
            'job_role' => 'Manager',
            'facebook' => null,
            'twitter' => null,
            'linkedin' => null,
            'instagram' => null,
            'avatar' => null,
            'phone_number' => null,
            'email_address' => null,
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/team_members', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'first_name',
                'last_name',
                'job_role',
                'facebook',
                'twitter',
                'linkedin',
                'instagram',
                'avatar',
            ],
            'meta' => [],
        ]);
    }

    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/team_members/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'first_name',
                'last_name',
                'job_role',
                'facebook',
                'twitter',
                'linkedin',
                'instagram',
                'avatar',
            ],
        ]);
    }

    public function testUpdateAction(): void
    {
        $data = [
            'first_name' => 'Joe',
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->patchJson('/api/team_members/2', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'first_name',
                'last_name',
                'job_role',
                'facebook',
                'twitter',
                'linkedin',
                'instagram',
                'avatar',
            ],
            'meta' => [],
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
        $response = $this->deleteJson('/api/team_members/2', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
        ]);
    }

    public function testReadAllByOrganisationAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/organisations/2/team_members', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'first_name',
                    'last_name',
                    'job_role',
                    'facebook',
                    'twitter',
                    'instagram',
                    'linkedin',
                    'avatar',
                ],
            ],
        ]);
    }

    public function testInspectByOrganisationAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/organisations/1/team_members/inspect', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'first_name',
                    'last_name',
                    'email_address',
                    'phone_number',
                    'job_role',
                    'facebook',
                    'twitter',
                    'instagram',
                    'linkedin',
                    'avatar',
                ],
            ],
        ]);
    }
}
