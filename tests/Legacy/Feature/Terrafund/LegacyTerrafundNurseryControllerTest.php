<?php

namespace Tests\Legacy\Feature\Terrafund;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class LegacyTerrafundNurseryControllerTest extends LegacyTestCase
{
    private function nurseryData($overrides = [])
    {
        return array_merge(
            [
                'name' => 'test name',
                'start_date' => '2000-01-01',
                'end_date' => '2038-01-28',
                'seedling_grown' => 12345,
                'planting_contribution' => 'the planting contribution',
                'nursery_type' => 'expanding',
                'terrafund_programme_id' => 1,
            ],
            $overrides
        );
    }

    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson(
            '/api/terrafund/nursery',
            $this->nurseryData(),
            $headers,
        )
        ->assertStatus(201)
        ->assertJsonFragment(
            $this->nurseryData()
        );
    }

    public function testCreateActionTypeHasToBeANurseryType(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson(
            '/api/terrafund/nursery',
            $this->nurseryData([
                'nursery_type' => 'not_valid',
            ]),
            $headers,
        )
        ->assertStatus(422);
    }

    public function testCreateActionUserMustBeInProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson(
            '/api/terrafund/nursery',
            $this->nurseryData([
                'terrafund_programme_id' => 2,
            ]),
            $headers,
        )
        ->assertStatus(403);
    }

    public function testCreateActionStartDateMustBeBeforeEndDate(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson(
            '/api/terrafund/nursery',
            $this->nurseryData([
                'start_date' => '2000-01-01',
                'end_date' => '1999-01-28',
            ]),
            $headers,
        )
        ->assertStatus(422);
    }

    public function testCreateActionRequiresBeingATerrafundUser(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson(
            '/api/terrafund/nursery',
            $this->nurseryData(),
            $headers,
        )
        ->assertStatus(403);
    }

    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/nursery/1', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
            'name' => 'Terrafund Nursery',
            'start_date' => '2020-01-01',
            'end_date' => '2021-01-01',
            'seedling_grown' => 123,
            'planting_contribution' => 'planting contribution',
            'nursery_type' => 'existing',
            'terrafund_programme_id' => 1,
        ])
        ->assertJsonPath('data.tree_species.0.id', 3)
        ->assertJsonPath('data.photos.0.id', 2);
    }

    public function testReadActionUserMustBeInNurseryProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/nursery/1', $headers)
        ->assertStatus(403);
    }
}
