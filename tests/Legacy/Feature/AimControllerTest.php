<?php

namespace Tests\Legacy\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class AimControllerTest extends LegacyTestCase
{
    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->getJson('/api/programme/1/aims', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'programme_id' => 1,
            'year_five_trees' => 1000,
            'restoration_hectares' => 1234,
            'survival_rate' => 50,
            'year_five_crown_cover' => 1000,
        ]);
    }

    public function testReadActionRequiresBelongingToProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->getJson('/api/programme/1/aims', $headers)
        ->assertStatus(403);
    }

    public function testReadActionWhenNoTargetsExist(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        User::where('id', 3)->firstOrFail()->programmes()->sync(2, false);

        $this->getJson('/api/programme/2/aims', $headers)
        ->assertStatus(404);
    }

    public function testUpdateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/programme/1/aims', [
            'year_five_trees' => 1234,
            'restoration_hectares' => 61000,
            'survival_rate' => 100,
            'year_five_crown_cover' => 1000,
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'programme_id' => 1,
            'year_five_trees' => 1234,
            'restoration_hectares' => 61000,
            'survival_rate' => 100,
            'year_five_crown_cover' => 1000,
        ]);
    }

    public function testUpdateActionRequiresBelongingToProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/programme/1/aims', [
            'year_five_trees' => 1234,
            'restoration_hectares' => 61000,
            'survival_rate' => 100,
            'year_five_crown_cover' => 1000,
        ], $headers)
        ->assertStatus(403);
    }
}
