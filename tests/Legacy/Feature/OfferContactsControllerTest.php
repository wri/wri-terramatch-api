<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class OfferContactsControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $this->callCreateActionWithUser();
        $this->callCreateActionWithTeamMember();
    }

    private function callCreateActionWithUser()
    {
        $data = [
            'offer_id' => 1,
            'user_id' => 3,
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/offer_contacts', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'offer_id',
                'first_name',
                'last_name',
            ],
            'meta' => [],
        ]);
    }

    private function callCreateActionWithTeamMember()
    {
        $data = [
            'offer_id' => 1,
            'team_member_id' => 1,
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/offer_contacts', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'team_member_id',
                'offer_id',
                'first_name',
                'last_name',
            ],
            'meta' => [],
        ]);
    }

    public function testReadAllByOfferAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/offers/2/offer_contacts', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'offer_id',
                    'first_name',
                    'last_name',
                ],
            ],
        ]);
    }

    public function testDeleteAction(): void
    {
        $data = [
            'offer_id' => 2,
            'team_member_id' => 3,
        ];
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/offer_contacts', $data, $headers);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->deleteJson('/api/offer_contacts/4', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson(['data' => []]);
    }
}
