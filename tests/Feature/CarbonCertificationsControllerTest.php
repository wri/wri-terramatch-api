<?php

namespace Tests\Feature;

use App\Models\CarbonCertification as CarbonCertificationModel;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CarbonCertificationsControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $data = [
            "pitch_id" => 1,
            "type" => "other",
            "other_value" => "foo bar baz",
            "link" => "www.example.com/carbon_certification.pdf"
        ];
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/carbon_certifications", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
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
                    "other_value",
                    "link",
                ]
            ]
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
        $response = $this->getJson("/api/carbon_certifications/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "pitch_id",
                "type",
                "other_value",
                "link",
            ]
        ]);
    }

    public function testUpdateAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $data = [
            "type" => "plan_vivo",
            "other_value" => null
        ];
        $response = $this->patchJson("/api/carbon_certifications/1", $data, $headers);
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
                    "other_value",
                    "link",
                ]
            ]
        ]);
    }

    public function testReadAllByPitchAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/pitches/1/carbon_certifications", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "pitch_id",
                    "type",
                    "other_value",
                    "link",
                ]
            ]
        ]);
    }

    public function testInspectByPitchAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/pitches/1/carbon_certifications/inspect", $headers);
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
                        "other_value",
                        "link",
                    ]
                ]
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
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->deleteJson("/api/carbon_certifications/1", [], $headers);
        $response->assertStatus(200);
        $this->assertNull(CarbonCertificationModel::find(1));
    }
}
