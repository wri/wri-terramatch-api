<?php

namespace Tests\Legacy\Feature;

use App\Jobs\ResetPasswordJob;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Legacy\LegacyTestCase;

final class AuthControllerTest extends LegacyTestCase
{
    public function testLoginAction(): void
    {
        $data = [
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ];
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/auth/login', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'token',
            ],
        ]);
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function testLogoutAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/auth/logout', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
        ]);
    }

    public function testRefreshAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/auth/refresh', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'token',
            ],
        ]);
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function testResendAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/auth/resend', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
        ]);
        $this->assertDatabaseHas('verifications', ['user_id' => 1]);
    }

    public function testVerifyAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $data = [
            'token' => 'ejyNeH1Rc26qNJeok932fGUv8GyNqMs4',
        ];
        $response = $this->patchJson('/api/auth/verify', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
        ]);
        $this->assertDatabaseMissing('verifications', ['user_id' => 1]);
    }

    public function testVerifyUnauthorizedAction(): void
    {
        $data = [
            'token' => 'fjyNeH1Rc26qNJeok932fGUv8GyNqMs4',
        ];
        $response = $this->patchJson('/api/v2/auth/verify', $data);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
        ]);
        $this->assertDatabaseMissing('verifications', ['user_id' => 2]);
    }

    public function testResetAction(): void
    {
        Queue::fake();

        $data = [
            'email_address' => 'jane@example.com',
        ];
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/auth/reset', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
        ]);

        Queue::assertPushed(ResetPasswordJob::class);
    }

    public function testResetActionAsAdmin(): void
    {
        Queue::fake();

        $data = [
            'email_address' => 'steve@example.com',
        ];
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->postJson('/api/auth/reset', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
        ]);

        Queue::assertPushed(ResetPasswordJob::class);
    }

    public function testChangeAction(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $data = [
            'token' => 'kmaBxJbn2NyfbLAIAAQtQGGdiJmyIblS',
            'password' => 'Password456',
        ];
        $response = $this->patchJson('/api/auth/change', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
        ]);
        $this->assertDatabaseMissing('password_resets', ['user_id' => 1]);
    }

    public function testMeAction(): void
    {
        $this->callMeActionAsAdmin();
        $this->callMeActionAsUser();
        $this->callMeActionAsUserWithNoApprovedOrganisation();
    }

    private function callMeActionAsAdmin()
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/auth/me', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'uuid',
                'first_name',
                'last_name',
                'email_address',
                'role',
//                'last_logged_in_at',
//                'email_address_verified_at',
            ],
        ]);
    }

    private function callMeActionAsUser()
    {
        $token = Auth::attempt([
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/auth/me', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'uuid',
//                'organisation_id',
//                'my_organisation',
//                'my_monitoring_organisations',
                'first_name',
                'last_name',
                'email_address',
                'role',
//                'email_address_verified_at',
//                'last_logged_in_at',
//                'twitter',
//                'facebook',
//                'linkedin',
//                'instagram',
//                'phone_number',
//                'has_ppc_projects',
//                'has_terrafund_projects',
            ],
//            'meta' => [],
        ]);
    }

    private function callMeActionAsUserWithNoApprovedOrganisation()
    {
        $token = Auth::attempt([
            'email_address' => 'ian@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/auth/me', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'uuid',
//                'organisation_id',
//                'organisation_name',
//                'my_organisation',
//                'my_monitoring_organisations',
                'first_name',
                'last_name',
                'email_address',
                'role',
//                'email_address_verified_at',
//                'last_logged_in_at',
//                'twitter',
//                'facebook',
//                'linkedin',
//                'instagram',
//                'phone_number',
            ],
//            'meta' => [],
        ]);
    }

    public function testDeleteMeActionSuccess(): void
    {
        $notAnAdmin = User::factory()->create();
        $userId = $notAnAdmin->id;

        $this->actingAs($notAnAdmin);

        $this->deleteJson(
            '/api/auth/delete_me',
            [],
            $this->getHeadersForUser($notAnAdmin->email_address)
        )
            ->assertStatus(200);

        $record = User::withTrashed()->find($userId);
        $this->assertIsInt($record->id);
        $this->assertNull($record->first_name);
        $this->assertNull($record->last_name);
        $this->assertNull($record->phone_number);
        $this->assertTrue(Str::isUuid($record->email_address));

        $record = User::find($userId);
        $this->assertNull($record);
    }

    public function testGuestDeleteMeActionFails(): void
    {
        $this->deleteJson('/api/auth/delete_me')
            ->assertStatus(401);
    }
}
