<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ElevatorVideosControllerTest extends TestCase
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
        foreach (["a", "b", "c"] as $variable) {
            $data = [
                "upload" => $this->fakeVideo()
            ];
            $response = $this->post("/api/uploads", $data, $headers);
            $$variable = $response->json("data.id");
        }
        $data = [
            "introduction" => $a,
            "aims" => $b,
            "importance" => $c
        ];
        $response = $this->postJson("/api/elevator_videos", $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "uploaded_at"
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
        $response = $this->getJson("/api/elevator_videos/1", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                "id",
                "upload_id",
                "preview",
                "status",
                "uploaded_at"
            ]
        ]);
    }
}
