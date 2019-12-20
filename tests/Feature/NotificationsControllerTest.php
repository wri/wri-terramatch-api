<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class NotificationsControllerTest extends TestCase
{
    public function testReadAllAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/notifications", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure(["data" => [
            [
                "id",
                "user_id",
                "title",
                "body",
                "unread",
                "created_at"
            ]
        ]]);
        $response->assertJson([
            "data" => [
                [
                    "unread" => true
                ]
            ]
        ]);
    }

    public function testMarkAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->patchJson("/api/notifications/1/mark", [], $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure(["data" => [
            "id",
            "user_id",
            "title",
            "body",
            "unread",
            "created_at"
        ]]);
        $response->assertJson(["data" => ["unread" => false]]);
    }
}
