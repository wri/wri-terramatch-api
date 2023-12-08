<?php

namespace Tests\Legacy\Feature;

use App\Models\User as UserModel;
use App\Models\V2\User as V2User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Tests\Legacy\LegacyTestCase;

final class UsersControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email_address' => 'john@example.com',
            'password' => 'Password123',
            'job_role' => 'Manager',
            'twitter' => null,
            'facebook' => null,
            'instagram' => null,
            'linkedin' => null,
            'phone_number' => '0123456789',
            'whatsapp_phone' => '0123456789',
        ];
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/users', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'first_name',
                'last_name',
                'email_address',
                'role',
                'email_address_verified_at',
                'last_logged_in_at',
                'twitter',
                'linkedin',
                'instagram',
                'facebook',
                'phone_number',
                'avatar',
                'whatsapp_phone',
            ],
            'meta' => [],
        ]);
        $response->assertJson([
            'data' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email_address' => 'john@example.com',
                'role' => 'user',
                'email_address_verified_at' => null,
                'last_logged_in_at' => null,
            ],
        ]);
    }

    /** @group slow */
    public function testInviteAction()
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $data = [
            'email_address' => 'laura@example.com',
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/users/invite', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'first_name',
                'last_name',
                'email_address',
                'role',
                'email_address_verified_at',
                'last_logged_in_at',
                'twitter',
                'linkedin',
                'instagram',
                'facebook',
                'phone_number',
                'avatar',
                'whatsapp_phone',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'first_name' => null,
                'last_name' => null,
                'email_address' => 'laura@example.com',
                'role' => 'user',
                'email_address_verified_at' => null,
                'last_logged_in_at' => null,
            ],
        ]);
    }

    public function testInviteActionAsTerrafundAdmin(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $data = [
            'email_address' => 'laura.terrafund@example.com',
            'role' => 'terrafund_admin',
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/users/invite', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'first_name',
                'last_name',
                'email_address',
                'role',
                'email_address_verified_at',
                'last_logged_in_at',
                'twitter',
                'linkedin',
                'instagram',
                'facebook',
                'phone_number',
                'avatar',
                'whatsapp_phone',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'first_name' => null,
                'last_name' => null,
                'email_address' => 'laura.terrafund@example.com',
                'role' => 'terrafund_admin',
                'email_address_verified_at' => null,
                'last_logged_in_at' => null,
            ],
        ]);
    }

    public function testAcceptAction(): void
    {
        $data = [
            'first_name' => 'Sue',
            'last_name' => 'Doe',
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
            'job_role' => 'Supervisor',
            'twitter' => null,
            'facebook' => null,
            'instagram' => null,
            'linkedin' => null,
            'phone_number' => '9876543210',
            'whatsapp_phone' => '9876543210',
        ];
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/users/accept', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'first_name',
                'last_name',
                'email_address',
                'role',
                'email_address_verified_at',
                'last_logged_in_at',
                'twitter',
                'linkedin',
                'instagram',
                'facebook',
                'phone_number',
                'avatar',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'first_name' => 'Sue',
                'last_name' => 'Doe',
                'email_address' => 'sue@example.com',
            ],
        ]);
    }

    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/users/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'first_name',
                'last_name',
                'email_address',
                'role',
                'email_address_verified_at',
                'last_logged_in_at',
                'twitter',
                'linkedin',
                'instagram',
                'facebook',
                'phone_number',
                'avatar',
                'whatsapp_phone',
            ],
            'meta' => [],
        ]);
    }

    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/users/all', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'organisation_id',
                    'first_name',
                    'last_name',
                    'email_address',
                    'role',
                    'email_address_verified_at',
                    'last_logged_in_at',
                    'twitter',
                    'linkedin',
                    'instagram',
                    'facebook',
                    'phone_number',
                    'avatar',
                    'whatsapp_phone',
                ],
            ],
            'meta' => [],
        ]);
    }

    public function testReadAllUnverifiedAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $this->getJson('/api/users/unverified', $headers)
        ->assertHeader('Content-Type', 'application/json')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'organisation_id',
                    'first_name',
                    'last_name',
                    'email_address',
                    'role',
                    'email_address_verified_at',
                    'last_logged_in_at',
                    'twitter',
                    'linkedin',
                    'instagram',
                    'facebook',
                    'phone_number',
                    'avatar',
                    'whatsapp_phone',
                ],
            ],
            'meta' => [],
        ])
        ->assertJsonFragment([
            'id' => 1,
            'email_address' => 'joe@example.com',
        ])
        ->assertJsonFragment([
            'id' => 7,
            'email_address' => 'sue@example.com',
        ])
        ->assertJsonMissing([
            'id' => 2,
            'name' => 'jane@example.com',
        ]);
    }

    public function testResendVerificationEmailAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);

        $user = V2User::find(7);
        $data = [
            'uuid' => $user ->uuid,
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->postJson('/api/users/resend', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);

        $data = [
            'uuid' => $user ->uuid,
            'callback_url' => 'https://testing-this.com',
        ];

        $response = $this->postJson('/api/users/resend', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
    }

    public function testUpdateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $data = [
            'first_name' => 'Stephen',
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->patchJson('/api/users/3', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'first_name',
                'last_name',
                'email_address',
                'role',
                'email_address_verified_at',
                'last_logged_in_at',
                'twitter',
                'linkedin',
                'instagram',
                'facebook',
                'phone_number',
                'avatar',
                'whatsapp_phone',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'first_name' => 'Stephen',
            ],
        ]);
    }

    public function testReadAllByOrganisationAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/organisations/2/users', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'first_name',
                    'last_name',
                    'twitter',
                    'linkedin',
                    'instagram',
                    'facebook',
                    'avatar',
                    'whatsapp_phone',
                ],
            ],
        ]);
    }

    public function testInspectByOrganisationAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/organisations/1/users/inspect', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'first_name',
                    'last_name',
                    'email_address',
                    'role',
                    'email_address_verified_at',
                    'last_logged_in_at',
                    'twitter',
                    'linkedin',
                    'instagram',
                    'facebook',
                    'phone_number',
                    'avatar',
                    'whatsapp_phone',
                ],
            ],
        ]);
    }

    public function testUnsubscribeAction(): void
    {
        $encryptedId = Crypt::encryptString('2');
        $response = $this->get('/admins/' . $encryptedId . '/unsubscribe');
        $response->assertStatus(302);
        $url = config('app.front_end'). '/unsubscribe';
        $response->assertHeader('Location', $url);
        $admin = UserModel::findOrFail(2);
        $this->assertFalse($admin->is_subscribed);
    }
}
