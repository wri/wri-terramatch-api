<?php

namespace Tests\Feature;

use App\Models\RestorationMethodMetric;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class RestorationMethodMetricsControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $data = [
            "pitch_id" => 1,
            "restoration_method" => "agroforestry",
            "experience" => 6,
            "land_size" => 10,
            "price_per_hectare" => 10,
            "biomass_per_hectare" => 1.23,
            "carbon_impact" => 1,
            "species_impacted" => [
                "Tiger",
                "Lion",
                "Leopard"
            ]
        ];
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/restoration_method_metrics", $data, $headers);
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
                    "restoration_method",
                    "experience",
                    "land_size",
                    "price_per_hectare",
                    "biomass_per_hectare",
                    "carbon_impact",
                    "species_impacted" => []
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
        $response = $this->getJson("/api/restoration_method_metrics/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "pitch_id",
                "restoration_method",
                "experience",
                "land_size",
                "price_per_hectare",
                "biomass_per_hectare",
                "carbon_impact",
                "species_impacted" => []
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
            "price_per_hectare" => 123
        ];
        $response = $this->patchJson("/api/restoration_method_metrics/1", $data, $headers);
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
                    "restoration_method",
                    "experience",
                    "land_size",
                    "price_per_hectare",
                    "biomass_per_hectare",
                    "carbon_impact",
                    "species_impacted" => []
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
        $response = $this->getJson("/api/pitches/1/restoration_method_metrics", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "pitch_id",
                    "restoration_method",
                    "experience",
                    "land_size",
                    "price_per_hectare",
                    "biomass_per_hectare",
                    "carbon_impact",
                    "species_impacted" => []
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
        $response = $this->getJson("/api/pitches/1/restoration_method_metrics/inspect", $headers);
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
                        "restoration_method",
                        "experience",
                        "land_size",
                        "price_per_hectare",
                        "biomass_per_hectare",
                        "carbon_impact",
                        "species_impacted" => []
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
        $response = $this->deleteJson("/api/restoration_method_metrics/1", [], $headers);
        $response->assertStatus(200);
        $this->assertNull(RestorationMethodMetric::find(1));
    }
}
