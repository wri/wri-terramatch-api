<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class MatchesControllerTest extends LegacyTestCase
{
    public function testReadAllAction(): void
    {
        $this->callReadAllActionAsFirstUser();
        $this->callReadAllActionAsSecondUser();
    }

    public function callReadAllActionAsFirstUser()
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/matches', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            [
                'id',
                'offer_id',
                'offer_name',
                'offer_interest_id',
                'offer_contacts' => [
                    [
                        'first_name',
                        'last_name',
                        'avatar',
                        'email_address',
                        'phone_number',
                    ],
                ],
                'pitch_id',
                'pitch_name',
                'pitch_interest_id',
                'pitch_contacts' => [
                    [
                        'first_name',
                        'last_name',
                        'avatar',
                        'email_address',
                        'phone_number',
                    ],
                ],
                'monitoring_id',
                'matched_at',
            ],
        ]]);
    }

    public function callReadAllActionAsSecondUser()
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/matches', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            [
                'id',
                'offer_id',
                'offer_name',
                'offer_interest_id',
                'offer_contacts' => [
                    [
                        'first_name',
                        'last_name',
                        'avatar',
                        'email_address',
                        'phone_number',
                    ],
                ],
                'pitch_id',
                'pitch_name',
                'pitch_interest_id',
                'pitch_contacts' => [
                    [
                        'first_name',
                        'last_name',
                        'avatar',
                        'email_address',
                        'phone_number',
                    ],
                ],
                'monitoring_id',
                'matched_at',
            ],
        ]]);
    }

    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/matches/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            'id',
            'offer_id',
            'offer_name',
            'offer_interest_id',
            'offer_contacts' => [
                [
                    'first_name',
                    'last_name',
                    'avatar',
                    'email_address',
                    'phone_number',
                ],
            ],
            'pitch_id',
            'pitch_name',
            'pitch_interest_id',
            'pitch_contacts' => [
                [
                    'first_name',
                    'last_name',
                    'avatar',
                    'email_address',
                    'phone_number',
                ],
            ],
            'monitoring_id',
            'matched_at',
        ]]);
    }
}
