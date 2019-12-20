<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use DateTime;

class AuthControllerTest extends TestCase
{
    public function testLoginAction()
    {
        $data = [
            "email_address" => "joe@example.com",
            "password" => "Password123",
        ];
        $headers = [
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/auth/login", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "token"
            ]
        ]);
        $this->assertNotEmpty($response->json("data.token"));
    }

    public function testLogoutAction()
    {
        $token = Auth::attempt([
            "email_address" => "joe@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/auth/logout", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson([
            "data" => []
        ]);
    }

    public function testRefreshAction()
    {
        $token = Auth::attempt([
            "email_address" => "joe@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/auth/refresh", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "token"
            ]
        ]);
        $this->assertNotEmpty($response->json("data.token"));
    }

    public function testResendAction()
    {
        $token = Auth::attempt([
            "email_address" => "joe@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/auth/resend", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson([
            "data" => []
        ]);
        $this->assertDatabaseHas("verifications", ["user_id" => 1]);
    }

    public function testVerifyAction()
    {
        $token = Auth::attempt([
            "email_address" => "joe@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $data = [
            "token" => "ejyNeH1Rc26qNJeok932fGUv8GyNqMs4"
        ];
        $response = $this->patchJson("/api/auth/verify", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson([
            "data" => []
        ]);
        $this->assertDatabaseMissing("verifications", ["user_id" => 1]);
    }

    public function testResetAction()
    {
        $data = [
            "email_address" => "jane@example.com"
        ];
        $headers = [
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/auth/reset", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson([
            "data" => []
        ]);
        $this->assertDatabaseHas("password_resets", ["user_id" => 2]);
    }

    public function testChangeAction()
    {
        $headers = [
            "Content-Type" => "application/json"
        ];
        $data = [
            "token" => "kmaBxJbn2NyfbLAIAAQtQGGdiJmyIblS",
            "password" => "Password456"
        ];
        $response = $this->patchJson("/api/auth/change", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson([
            "data" => []
        ]);
        $this->assertDatabaseMissing("password_resets", ["user_id" => 1]);
    }

    public function testMeAction()
    {
        $this->callMeActionAsAdmin();
        $this->callMeActionAsUser();
    }

    private function callMeActionAsAdmin()
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/auth/me", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "first_name",
                "last_name",
                "email_address",
                "role",
                "last_logged_in_at",
                "email_address_verified_at"
            ]
        ]);
    }

    private function callMeActionAsUser()
    {
        $token = Auth::attempt([
            "email_address" => "joe@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/auth/me", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "organisation_id",
                "first_name",
                "last_name",
                "email_address",
                "role",
                "email_address_verified_at",
                "last_logged_in_at",
                "twitter",
                "facebook",
                "linkedin",
                "instagram",
                "phone_number"
            ],
            "meta" => []
        ]);
    }
}
