<?php

namespace Tests\Legacy\Feature;

use App\Models\Admin as AdminModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Tests\Legacy\LegacyTestCase;

final class AdminsControllerTest extends LegacyTestCase
{
    public function testInviteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $data = [
            'email_address' => 'anna@example.com',
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/admins/invite', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'email_address',
                'role',
                'email_address_verified_at',
                'last_logged_in_at',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'first_name' => null,
                'last_name' => null,
                'email_address' => 'anna@example.com',
                'role' => 'admin',
                'email_address_verified_at' => null,
                'last_logged_in_at' => null,
            ],
        ]);
    }

    public function testAcceptAction(): void
    {
        $data = [
            'first_name' => 'Tom',
            'last_name' => 'Doe',
            'email_address' => 'tom@example.com',
            'password' => 'Password123',
            'job_role' => 'Manager',
        ];
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/admins/accept', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'email_address',
                'role',
                'email_address_verified_at',
                'last_logged_in_at',
                'job_role',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'first_name' => 'Tom',
                'last_name' => 'Doe',
                'email_address' => 'tom@example.com',
            ],
        ]);
    }

    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/admins/2', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'email_address',
                'role',
                'last_logged_in_at',
                'email_address_verified_at',
            ],
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
        $response = $this->getJson('/api/admins', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonCount(4, 'data');
        $response->assertJsonFragment([
            'id' => 2,
            'role' => 'admin',
        ]);
        $response->assertJsonFragment([
            'id' => 5,
            'role' => 'admin',
        ]);
        $response->assertJsonFragment([
            'id' => 15,
            'role' => 'terrafund_admin',
        ]);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'first_name',
                    'last_name',
                    'email_address',
                    'role',
                    'last_logged_in_at',
                    'email_address_verified_at',
                ],
            ],
            'meta' => [
                'count',
            ],
        ]);
    }

    public function testUpdateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $data = [
            'first_name' => 'Joan',
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->patchJson('/api/admins/2', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'email_address',
                'role',
                'email_address_verified_at',
                'last_logged_in_at',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'first_name' => 'Joan',
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
        $admin = AdminModel::findOrFail(2);
        $this->assertFalse($admin->is_subscribed);
    }
}
