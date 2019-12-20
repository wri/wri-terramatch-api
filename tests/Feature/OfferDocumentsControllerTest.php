<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class OfferDocumentsControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $data = [
            "upload" => $this->fakeFile()
        ];
        $response = $this->post("/api/uploads", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
        $id = json_decode($response->getContent())->data->id;
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $data = [
            "offer_id" => 1,
            "name" => "Foo",
            "type" => "legal",
            "document" => $id
        ];
        $response = $this->postJson("/api/offer_documents", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "offer_id",
                "name",
                "type",
                "document"
            ],
            "meta" => []
        ]);
    }

    public function testReadAllByOfferAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/offers/1/offer_documents", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "offer_id",
                    "name",
                    "type",
                    "document"
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
        $response = $this->getJson("/api/offer_documents/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "offer_id",
                "name",
                "type",
                "document"
            ],
            "meta" => []
        ]);
    }

    public function testUpdateAction()
    {
        $data = [
            "name" => "Bar"
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->patchJson("/api/offer_documents/1", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "offer_id",
                "name",
                "type",
                "document"
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
        $response = $this->deleteJson("/api/offer_documents/1", [], $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson(["data" => []]);
    }
}
