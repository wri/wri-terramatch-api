<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class MatchesControllerTest extends TestCase
{
    public function testReadAllAction()
    {
        Artisan::call("find-matches");
        $this->callReadAllActionAsFirstUser();
        $this->callReadAllActionAsSecondUser();
    }

    public function callReadAllActionAsFirstUser()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/matches", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure(["data" => [
            [
                "id",
                "offer_id",
                "offer_interest_id",
                "offer_contacts" => [
                    [
                        "first_name",
                        "last_name",
                        "avatar",
                        "email_address",
                        "phone_number"
                    ]
                ],
                "pitch_id",
                "pitch_interest_id",
                "pitch_contacts" => [
                    [
                        "first_name",
                        "last_name",
                        "avatar",
                        "email_address",
                        "phone_number"
                    ]
                ],
                "matched_at"
            ]
        ]]);
    }

    public function callReadAllActionAsSecondUser()
    {
        $token = Auth::attempt([
            "email_address" => "andrew@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/matches", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure(["data" => [
            [
                "id",
                "offer_id",
                "offer_interest_id",
                "offer_contacts" => [
                    [
                        "first_name",
                        "last_name",
                        "avatar",
                        "email_address",
                        "phone_number"
                    ]
                ],
                "pitch_id",
                "pitch_interest_id",
                "pitch_contacts" => [
                    [
                        "first_name",
                        "last_name",
                        "avatar",
                        "email_address",
                        "phone_number"
                    ]
                ],
                "matched_at"
            ]
        ]]);
    }

    public function testReadAction()
    {
        Artisan::call("find-matches");
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/matches/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure(["data" => [
            "id",
            "offer_id",
            "offer_interest_id",
            "offer_contacts" => [
                [
                    "first_name",
                    "last_name",
                    "avatar",
                    "email_address",
                    "phone_number"
                ]
            ],
            "pitch_id",
            "pitch_interest_id",
            "pitch_contacts" => [
                [
                    "first_name",
                    "last_name",
                    "avatar",
                    "email_address",
                    "phone_number"
                ]
            ],
            "matched_at"
        ]]);
    }
}
