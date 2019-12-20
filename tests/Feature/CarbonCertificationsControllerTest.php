<?php

namespace Tests\Feature;

use App\Models\CarbonCertification;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

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
            "other_type" => "Other Type",
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
                "data" => [
                    "id",
                    "pitch_id",
                    "type",
                    "other_type",
                    "link",
                ]
            ]
        ]);
        $content = $response->decodeResponseJson();
        $this->assertEquals('Other Type', $content['data']['data']['other_type']);
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
                "other_type",
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
            "type" => "other",
            "other_type" => "Updated other type"
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
                "data" => [
                    "id",
                    "pitch_id",
                    "type",
                    "other_type",
                    "link",
                ]
            ]
        ]);
        $content = $response->decodeResponseJson();
        $this->assertEquals('Updated other type', $content['data']['data']['other_type']);
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
                    "other_type",
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
                    "data" => [
                        "id",
                        "pitch_id",
                        "type",
                        "other_type",
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
        $this->assertNull(CarbonCertification::find(1));
    }
}
