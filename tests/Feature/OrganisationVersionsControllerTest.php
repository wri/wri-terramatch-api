<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrganisationVersionsControllerTest extends TestCase
{
    public function testReadAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/organisation_versions/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "data" => [
                    "id",
                    "name",
                    "description",
                    "address_1",
                    "address_2",
                    "city",
                    "state",
                    "zip_code",
                    "country",
                    "phone_number",
                    "website",
                    "type",
                    "category",
                    "facebook",
                    "twitter",
                    "linkedin",
                    "instagram",
                    "cover_photo",
                    "avatar",
                    "video",
                    "founded_at"
                ]
            ]
        ]);
    }

    public function testApproveAction()
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $data = [];
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->patchJson("/api/organisation_versions/2/approve", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "data" => [
                    "id",
                    "name",
                    "description",
                    "address_1",
                    "address_2",
                    "city",
                    "state",
                    "zip_code",
                    "country",
                    "phone_number",
                    "website",
                    "type",
                    "category",
                    "facebook",
                    "twitter",
                    "linkedin",
                    "instagram",
                    "cover_photo",
                    "avatar",
                    "video",
                    "founded_at"
                ]
            ]
        ]);
        $response->assertJson([
            "data" => [
                "status" => "approved",
            ]
        ]);
    }

    public function testRejectAction()
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $data = [
            "rejected_reason" => "Lorem ipsum dolor sit amet"
        ];
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->patchJson("/api/organisation_versions/2/reject", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "data" => [
                    "id",
                    "name",
                    "description",
                    "address_1",
                    "address_2",
                    "city",
                    "state",
                    "zip_code",
                    "country",
                    "phone_number",
                    "website",
                    "type",
                    "category",
                    "facebook",
                    "twitter",
                    "linkedin",
                    "instagram",
                    "cover_photo",
                    "avatar",
                    "video",
                    "founded_at"
                ]
            ]
        ]);
        $response->assertJson([
            "data" => [
                "status" => "rejected",
            ]
        ]);
    }

    public function testDeleteAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->deleteJson("/api/organisation_versions/2", [], $headers);
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
        $response = $this->getJson("/api/organisations/1/organisation_versions", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "status",
                    "approved_rejected_by",
                    "approved_rejected_at",
                    "rejected_reason",
                    "data" => [
                        "id",
                        "name",
                        "description",
                        "address_1",
                        "address_2",
                        "city",
                        "state",
                        "zip_code",
                        "country",
                        "phone_number",
                        "website",
                        "type",
                        "category",
                        "facebook",
                        "twitter",
                        "linkedin",
                        "instagram",
                        "avatar",
                        "cover_photo",
                        "founded_at"
                    ]
                ]
            ]
        ]);
    }
}
