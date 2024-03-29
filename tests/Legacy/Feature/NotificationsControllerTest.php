<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class NotificationsControllerTest extends LegacyTestCase
{
    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/notifications', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            [
                'id',
                'user_id',
                'title',
                'body',
                'unread',
                'created_at',
            ],
        ]]);
        $response->assertJson([
            'data' => [
                [
                    'unread' => true,
                ],
            ],
        ]);
    }

    public function testMarkAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->patchJson('/api/notifications/1/mark', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            'id',
            'user_id',
            'title',
            'body',
            'unread',
            'created_at',
        ]]);
        $response->assertJson(['data' => ['unread' => false]]);
    }
}
