<?php

namespace Tests\Feature;

use App\Models\PitchDocument;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class PitchDocumentsControllerTest extends TestCase
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
        $data = [
            "pitch_id" => 1,
            "name" => "Example Document",
            "type" => "award",
            "document" => $id
        ];
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/pitch_documents", $data, $headers);
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
        $response = $this->getJson("/api/pitch_documents/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "pitch_id",
                "name",
                "type",
                "document"
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
            "name" => "Example Document"
        ];
        $response = $this->patchJson("/api/pitch_documents/1", $data, $headers);
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
                    "name",
                    "type",
                    "document"
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
        $response = $this->getJson("/api/pitches/1/pitch_documents", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "pitch_id",
                    "name",
                    "type",
                    "document"
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
        $response = $this->getJson("/api/pitches/1/pitch_documents/inspect", $headers);
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
                        "type",
                        "document",
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
        $response = $this->deleteJson("/api/pitch_documents/1", [], $headers);
        $response->assertStatus(200);
        $this->assertNull(PitchDocument::find(1));
    }
}
