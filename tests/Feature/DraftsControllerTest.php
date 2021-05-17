<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DraftsControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $data = [
            "name" => "Example Draft",
            "type" => "offer"
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/drafts", $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                "id",
                "organisation_id",
                "name",
                "type",
                "data",
                "created_at",
                "created_by",
                "updated_at",
                "updated_by"
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
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->getJson("/api/drafts/1", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                "id",
                "organisation_id",
                "name",
                "type",
                "data",
                "created_at",
                "created_by",
                "updated_at",
                "updated_by"
            ]
        ]);
    }

    public function testReadAllByTypeAction()
    {
        $this->callReadAllByTypeActionAsOffer();
        $this->callReadAllByTypeActionAsPitch();
    }

    private function callReadAllByTypeActionAsOffer()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->getJson("/api/drafts/offers", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "organisation_id",
                    "name",
                    "type",
                    "data",
                    "created_at",
                    "created_by",
                    "updated_at",
                    "updated_by"
                ]
            ]
        ]);
    }

    private function callReadAllByTypeActionAsPitch()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->getJson("/api/drafts/pitches", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "organisation_id",
                    "name",
                    "type",
                    "data",
                    "created_at",
                    "created_by",
                    "updated_at",
                    "updated_by"
                ]
            ]
        ]);
    }

    public function testUpdateAction()
    {
        $data = [
            [
                "op" => "add",
                "path" => "/offer/name",
                "value" => "foo"
            ]
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->patchJson("/api/drafts/1", $data, $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                "id",
                "organisation_id",
                "name",
                "type",
                "data",
                "created_at",
                "created_by",
                "updated_at",
                "updated_by"
            ]
        ]);
        $response->assertJson([
            "data" => [
                "data" => [
                    "offer" => [
                        "name" => "foo"
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
        $response = $this->deleteJson("/api/drafts/1", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJson([
            "data" => []
        ]);
    }

    public function testPublishAction()
    {
        $this->callPublishActionAsOffer();
        $this->callPublishActionAsPitch();
    }

    private function callPublishActionAsOffer()
    {
        $data = [];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->patchJson("/api/drafts/3/publish", $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                "offer_id"
            ]
        ]);
    }

    private function callPublishActionAsPitch()
    {
        $data = [];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->patchJson("/api/drafts/4/publish", $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                "pitch_id"
            ]
        ]);
    }
}
