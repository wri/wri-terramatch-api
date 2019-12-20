<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class UsersControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $data = [
            "first_name" => "John",
            "last_name" => "Doe",
            "email_address" => "john@example.com",
            "password" => "Password123",
            "job_role" => "Manager",
            "twitter" => null,
            "facebook" => null,
            "instagram" => null,
            "linkedin" => null,
            "phone_number" => "0123456789"
        ];
        $headers = [
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/users", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
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
                "linkedin",
                "instagram",
                "facebook",
                "phone_number",
                "avatar"
            ],
            "meta" => []
        ]);
        $response->assertJson([
            "data" => [
                "first_name" => "John",
                "last_name" => "Doe",
                "email_address" => "john@example.com",
                "role" => "user",
                "email_address_verified_at" => null,
                "last_logged_in_at" => null
            ]
        ]);
    }

    public function testInviteAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $data = [
            "email_address" => "laura@example.com"
        ];
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/users/invite", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
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
                "linkedin",
                "instagram",
                "facebook",
                "phone_number",
                "avatar"
            ]
        ]);
        $response->assertJson([
            "data" => [
                "first_name" => null,
                "last_name" => null,
                "email_address" => "laura@example.com",
                "role" => "user",
                "email_address_verified_at" => null,
                "last_logged_in_at" => null
            ]
        ]);
    }

    public function testAcceptAction()
    {
        $data = [
            "first_name" => "Sue",
            "last_name" => "Doe",
            "email_address" => "sue@example.com",
            "password" => "Password123",
            "job_role" => "Supervisor",
            "twitter" => null,
            "facebook" => null,
            "instagram" => null,
            "linkedin" => null,
            "phone_number" => "9876543210"
        ];
        $headers = [
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/users/accept", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
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
                "linkedin",
                "instagram",
                "facebook",
                "phone_number",
                "avatar"
            ]
        ]);
        $response->assertJson([
            "data" => [
                "first_name" => "Sue",
                "last_name" => "Doe",
                "email_address" => "sue@example.com"
            ]
        ]);
    }

    public function testReadAction()
    {
        $token = Auth::attempt([
            "email_address" => "joe@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/users/1", $headers);
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
                "linkedin",
                "instagram",
                "facebook",
                "phone_number",
                "avatar"
            ],
            "meta" => []
        ]);
    }

    public function testUpdateAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $data = [
            "first_name" => "Stephen"
        ];
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->patchJson("/api/users/3", $data, $headers);
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
                "linkedin",
                "instagram",
                "facebook",
                "phone_number",
                "avatar"
            ]
        ]);
        $response->assertJson([
            "data" => [
                "first_name" => "Stephen"
            ]
        ]);
    }

    public function testReadAllByOrganisationAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/organisations/1/users", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "organisation_id",
                    "first_name",
                    "last_name",
                    "email_address",
                    "role",
                    "email_address_verified_at",
                    "last_logged_in_at",
                    "twitter",
                    "linkedin",
                    "instagram",
                    "facebook",
                    "phone_number",
					"avatar"
                ]
            ]
        ]);
    }
}
