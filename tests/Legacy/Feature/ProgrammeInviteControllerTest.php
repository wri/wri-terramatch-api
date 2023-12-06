<?php

namespace Tests\Legacy\Feature;

use App\Mail\ProgrammeInviteReceived;
use App\Mail\UserInvited;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\Legacy\LegacyTestCase;

final class ProgrammeInviteControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        Mail::fake();

        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/programme/1/invite', [
            'email_address' => 'sue@example.com',
            'callback_url' => 'https://testing-this.com/',
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'programme_id' => 1,
            'accepted_at' => null,
            'email_address' => 'sue@example.com',
        ]);

        Mail::assertQueued(ProgrammeInviteReceived::class, function ($mail) {
            return $mail->hasTo('sue@example.com');
        });

        Mail::assertNotQueued(UserInvited::class, function ($mail) {
            return $mail->hasTo('a.new.user@email.com');
        });
    }

    public function testCreateActionWhenUserIsAlreadyPartOfProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/programme/1/invite', [
            'email_address' => 'monitoring.partner.3@monitor.com',
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionWithNewTerramatchUser(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::createFromDate('2021-07-23'));

        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/programme/1/invite', [
            'email_address' => 'a.new.user@email.com',
            'callback_url' => 'https://testing-this.com/',
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'programme_id' => 1,
            'accepted_at' => '2021-07-23T00:00:00.000000Z',
            'email_address' => 'a.new.user@email.com',
        ]);

        Mail::assertNotQueued(ProgrammeInviteReceived::class, function ($mail) {
            return $mail->hasTo('a.new.user@email.com');
        });

        Mail::assertQueued(UserInvited::class, function ($mail) {
            return $mail->hasTo('a.new.user@email.com');
        });
    }

    public function testCreateActionEmailAddressIsRequired(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/programme/1/invite', $headers)
        ->assertStatus(422);
    }

    public function testCreateActionProgrammeIdHasToExist(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/programme/6102000/invite', [
            'email_address' => 'a.new.user@email.com',
        ], $headers)
        ->assertStatus(404);
    }

    public function testAcceptAction(): void
    {
        Carbon::setTestNow(Carbon::createFromDate('2021-07-23'));
        $token = Auth::attempt([
            'email_address' => 'monitoring.partner.1@monitor.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'token' => 'tlvOSFc5kpR2VqrCUiwI3gabz5OeLr7LdUmhyyF693agCu7fyW9d8p4pBtEGORmj',
        ];

        $response = $this->postJson('/api/programme/invite/accept', $data, $headers);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => 1,
            'programme_id' => 1,
            'accepted_at' => '2021-07-23T00:00:00.000000Z',
        ]);

        $this->assertDatabaseHas('programme_user', [
            'user_id' => 8,
            'programme_id' => 1,
            'is_monitoring' => true,
        ]);
    }

    public function testAcceptActionRequiresEmailAndTokenToMatch(): void
    {
        $token = Auth::attempt([
            'email_address' => 'monitoring.partner.2@monitor.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'token' => 'tlvOSFc5kpR2VqrCUiwI3gabz5OeLr7LdUmhyyF693agCu7fyW9d8p4pBtEGORmj',
        ];

        $this->postJson('/api/programme/invite/accept', $data, $headers)
        ->assertStatus(404);
    }

    public function testAcceptActionRequiresUnacceptedInvite(): void
    {
        $token = Auth::attempt([
            'email_address' => 'monitoring.partner.1@monitor.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'token' => 'QhiKk66GX9fkLaEZY06T6KLEw8ALhPkeBtmN5e9wgNo48cSmmhlRlFrczRjLtz3S',
        ];

        $this->postJson('/api/programme/invite/accept', $data, $headers)
        ->assertStatus(422);
    }

    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/programme/1/partners', $headers);
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
                ],
            ],
        ]);
    }

    public function testRemoveUserAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'monitoring.partner.1@monitor.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'user_id' => 3,
            'programme_id' => 1,
        ];

        $response = $this->deleteJson('/api/programme/invite/remove', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => []]);
    }

    public function testDeleteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->deleteJson('/api/programme/invite/2', $headers)
        ->assertStatus(200);
    }

    public function testDeleteActionRequiresBeingPartOfProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->deleteJson('/api/programme/invite/2', $headers)
        ->assertStatus(403);
    }
}
