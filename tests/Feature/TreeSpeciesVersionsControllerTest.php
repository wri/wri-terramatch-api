<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class TreeSpeciesVersionsControllerTest extends TestCase
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
        $response = $this->getJson("/api/tree_species_versions/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "data" => [
                    "name",
                    "is_native",
                    "count",
                    "price_to_plant",
                    "price_to_maintain",
                    "saplings",
                    "site_prep",
                    "survival_rate",
                    "produces_food",
                    "produces_firewood",
                    "produces_timber",
                    "owner",
                    "season"
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
        $response = $this->patchJson("/api/tree_species_versions/2/approve", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "data" => [
                    "name",
                    "is_native",
                    "count",
                    "price_to_plant",
                    "price_to_maintain",
                    "saplings",
                    "site_prep",
                    "survival_rate",
                    "produces_food",
                    "produces_firewood",
                    "produces_timber",
                    "owner",
                    "season"
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
        $response = $this->patchJson("/api/tree_species_versions/2/reject", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "data" => [
                    "name",
                    "is_native",
                    "count",
                    "price_to_plant",
                    "price_to_maintain",
                    "saplings",
                    "site_prep",
                    "survival_rate",
                    "produces_food",
                    "produces_firewood",
                    "produces_timber",
                    "owner",
                    "season"
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
        $response = $this->deleteJson("/api/tree_species_versions/2", [], $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson([
            "data" => []
        ]);
    }

    public function testReadAllByTreeSpeciesAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/tree_species/1/tree_species_versions", $headers);
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
                        "name",
                        "is_native",
                        "count",
                        "price_to_plant",
                        "price_to_maintain",
                        "saplings",
                        "site_prep",
                        "survival_rate",
                        "produces_food",
                        "produces_firewood",
                        "produces_timber",
                        "owner",
                        "season"
                    ]
                ]
            ]
        ]);
    }
}
