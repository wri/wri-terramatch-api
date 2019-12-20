<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class OfferContactsControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $this->callCreateActionWithUser();
        $this->callCreateActionWithTeamMember();
    }

    private function callCreateActionWithUser()
    {
        $data = [
            "offer_id" => 1,
            "user_id" => 3
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/offer_contacts", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "offer_id",
                "user_id"
            ],
            "meta" => []
        ]);
    }

    private function callCreateActionWithTeamMember()
    {
        $data = [
            "offer_id" => 1,
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
        $response = $this->postJson("/api/offer_contacts", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "offer_id",
                "team_member_id"
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
        $response = $this->getJson("/api/offers/2/offer_contacts", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "offer_id"
                ]
            ]
        ]);
        $data = json_decode($response->getContent());
        foreach ($data->data as $offerContact) {
            $this->assertTrue(
                array_key_exists("user_id", $offerContact) XOR
                array_key_exists("team_member_id", $offerContact)
            );
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
        $response = $this->deleteJson("/api/offer_contacts/1", [], $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJson(["data" => []]);
    }
}
