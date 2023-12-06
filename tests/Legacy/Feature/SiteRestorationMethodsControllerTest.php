<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class SiteRestorationMethodsControllerTest extends LegacyTestCase
{
    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/site/restoration_methods', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
            'name' => 'Mangrove Tree Restoration',
            'key' => 'mangrove_tree_restoration',
        ])
        ->assertJsonFragment([
            'id' => 2,
            'name' => 'Assisted Natural Regeneration',
            'key' => 'assisted_natural_regeneration',
        ]);
    }
}
