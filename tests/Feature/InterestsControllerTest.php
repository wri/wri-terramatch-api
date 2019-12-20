<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class InterestsControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $data = [
            "initiator" => "offer",
            "offer_id" => 3,
            "pitch_id" => 1
        ];
        $token = Auth::attempt([
            "email_address" => "andrew@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/interests", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "organisation_id",
                "offer_id",
                "pitch_id",
                "initiator",
                "matched",
                "created_at"
            ],
            "meta" => []
        ]);
    }

    public function testReadAllByTypeAction()
    {
        $this->callReadAllActionAsInitiated();
        $this->callReadAllActionAsReceived();
    }

    public function callReadAllActionAsInitiated()
    {
        $token = Auth::attempt([
            "email_address" => "andrew@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/interests/initiated", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "organisation_id",
                    "offer_id",
                    "pitch_id",
                    "initiator",
                    "matched",
                    "created_at"
                ]
            ]
        ]);
        foreach ($response->decodeResponseJson("data") as $interest) {
            $this->assertSame(2, $interest["organisation_id"]);
        }
    }

    public function callReadAllActionAsReceived()
    {
        $token = Auth::attempt([
            "email_address" => "andrew@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/interests/received", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "organisation_id",
                    "offer_id",
                    "pitch_id",
                    "initiator",
                    "matched",
                    "created_at"
                ]
            ]
        ]);
        foreach ($response->decodeResponseJson("data") as $interest) {
            $this->assertNotSame(2, $interest["organisation_id"]);
        }
    }

    public function testDeleteAction()
    {
        $token = Auth::attempt([
            "email_address" => "andrew@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->deleteJson("/api/interests/1", [], $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson(["data" => []]);
    }
}
