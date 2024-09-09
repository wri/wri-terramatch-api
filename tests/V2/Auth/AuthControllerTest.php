<?php

namespace Tests\V2\Auth;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_auth_me_action()
    {
        $primeOrg = Organisation::factory(['status' => Organisation::STATUS_APPROVED])->create();
        $monitoringOrgs = Organisation::factory(['status' => Organisation::STATUS_APPROVED])->count(2)->create();
        $user = User::factory()->create(['organisation_id' => $primeOrg->id, 'locale' => 'en-US']);
        foreach ($monitoringOrgs as $monitoringOrg) {
            $monitoringOrg->partners()->attach($user, ['status' => 'approved']);
        }

        $r = $this->actingAs($user)
            ->getJson('/api/auth/me')
            ->assertSuccessful();
        //            ->assertJsonCount(2, 'data.my_monitoring_organisations');
    }

    public function test_resend_by_email_action(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson('/api/v2/users/resend', [
            'email_address' => $user->email_address,
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [],
            ]);
        $this->assertDatabaseHas('verifications', ['user_id' => $user->id]);
    }
}
