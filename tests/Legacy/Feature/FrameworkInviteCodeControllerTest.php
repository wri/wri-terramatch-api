<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class FrameworkInviteCodeControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/framework/access_code', [
            'code' => 'gfd432',
            'framework' => 'ppc',
        ], $headers)
            ->assertStatus(201);
    }

    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = $this->getJson('/api/framework/access_code/all', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'code',
                    'framework_id',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [],
        ]);
    }

    public function testDeleteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->deleteJson('/api/framework/access_code/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => []]);
    }

    public function testJoinAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/framework/access_code/join', [
            'code' => 'kcs0611',
        ], $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
            'code' => 'kcs0611',
        ]);

        $this->assertDatabaseHas('framework_user', [
            'user_id' => 4,
            'framework_id' => 1,
        ]);
    }

    public function testJoinActionInvalidCodeThrowsError(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/framework/access_code/join', [
            'code' => 'notavalidcodeohno',
        ], $headers)
        ->assertStatus(422);
    }
}
