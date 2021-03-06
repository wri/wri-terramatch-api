<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CarbonCertificationVersionsControllerTest extends TestCase
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
        $response = $this->getJson("/api/carbon_certification_versions/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "rejected_reason_body",
                "data" => [
                    "id",
                    "pitch_id",
                    "type",
                    "link"
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
        $response = $this->patchJson("/api/carbon_certification_versions/2/approve", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "rejected_reason_body",
                "data" => [
                    "id",
                    "pitch_id",
                    "type",
                    "link"
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
    {;
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $data = [
            "rejected_reason" => "cannot_verify",
            "rejected_reason_body" => "Lorem ipsum dolor sit amet"
        ];
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->patchJson("/api/carbon_certification_versions/2/reject", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "rejected_reason_body",
                "data" => [
                    "id",
                    "pitch_id",
                    "type",
                    "link"
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
        $response = $this->deleteJson("/api/carbon_certification_versions/2", [], $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson([
            "data" => []
        ]);
    }

    public function testReadAllByCarbonCertificationAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/carbon_certifications/1/carbon_certification_versions", $headers);
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
                    "rejected_reason_body",
                    "data" => [
                        "id",
                        "pitch_id",
                        "type",
                        "link"
                    ]
                ]
            ]
        ]);
    }

    public function testReviveAction()
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
        $response = $this->patchJson("/api/carbon_certification_versions/3/revive", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "rejected_reason_body",
                "data" => [
                    "id",
                    "pitch_id",
                    "type",
                    "link"
                ]
            ]
        ]);
        $response->assertJson([
            "data" => [
                "status" => "approved",
            ]
        ]);
    }
}
