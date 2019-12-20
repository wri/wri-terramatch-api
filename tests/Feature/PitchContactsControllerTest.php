<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class PitchContactsControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $this->callCreateActionWithUser();
        $this->callCreateActionWithTeamMember();
    }

    private function callCreateActionWithUser()
    {
        $data = [
            "pitch_id" => 1,
            "user_id" => 6
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/pitch_contacts", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "pitch_id",
                "user_id"
            ],
            "meta" => []
        ]);
    }

    private function callCreateActionWithTeamMember()
    {
        $data = [
            "pitch_id" => 1,
            "team_member_id" => 1
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/pitch_contacts", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "pitch_id",
                "team_member_id"
            ],
            "meta" => []
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
        $response = $this->getJson("/api/pitches/1/pitch_contacts", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "pitch_id"
                ]
            ]
        ]);
        $data = json_decode($response->getContent());
        foreach ($data->data as $pitchContact) {
            $this->assertTrue(
                array_key_exists("user_id", $pitchContact) XOR
                array_key_exists("team_member_id", $pitchContact)
            );
        }
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
        $response = $this->deleteJson("/api/pitch_contacts/1", [], $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson(["data" => []]);
    }
}
