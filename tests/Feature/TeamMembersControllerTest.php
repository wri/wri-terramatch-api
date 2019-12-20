<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class TeamMembersControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $data = [
            "first_name" => "Oliver",
            "last_name" => "Smith",
            "job_role" => "Manager",
            "facebook" => null,
            "twitter" => null,
            "linkedin" => null,
            "instagram" => null,
            "avatar" => null,
            "phone_number" => null,
            "email_address" => null
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/team_members", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "organisation_id",
                "first_name",
                "last_name",
                "job_role",
                "facebook",
                "twitter",
                "linkedin",
                "instagram",
                "avatar"
            ],
            "meta" => []
        ]);
    }

    public function testReadAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/team_members/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "organisation_id",
                "first_name",
                "last_name",
                "job_role",
                "facebook",
                "twitter",
                "linkedin",
                "instagram",
                "avatar"
            ]
        ]);
    }

    public function testUpdateAction()
    {
        $data = [
            "first_name" => "Joe"
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->patchJson("/api/team_members/2", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "organisation_id",
                "first_name",
                "last_name",
                "job_role",
                "facebook",
                "twitter",
                "linkedin",
                "instagram",
                "avatar"
            ],
            "meta" => []
        ]);
    }

    public function testDeleteAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->deleteJson("/api/team_members/2", [], $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson([
            "data" => []
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
        $response = $this->getJson("/api/organisations/1/team_members", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "first_name",
                    "last_name",
                    "job_role",
                    "facebook",
                    "twitter",
                    "instagram",
                    "linkedin",
                    "avatar"
                ]
            ]
        ]);
    }
}
