<?php

namespace Tests\Legacy\Feature\Terrafund;

use App\Mail\TerrafundProgrammeInviteReceived;
use App\Mail\UserInvited;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\Legacy\LegacyTestCase;

final class TerrafundProgrammeInviteControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        Mail::fake();

        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/terrafund/programme/1/invite', [
            'email_address' => 'sue@example.com',
            'callback_url' => 'https://testing-this.com/',
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'terrafund_programme_id' => 1,
            'email_address' => 'sue@example.com',
        ]);

        Mail::assertQueued(TerrafundProgrammeInviteReceived::class, function ($mail) {
            return $mail->hasTo('sue@example.com');
        });
    }

    public function testCreateActionWhenUserIsAlreadyPartOfProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/terrafund/programme/1/invite', [
            'email_address' => 'terrafund@example.com',
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionWithNewTerramatchUser(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::createFromDate('2021-07-23'));

        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/terrafund/programme/1/invite', [
            'email_address' => 'a.new.user@email.com',
            'callback_url' => 'https://testing-this.com/',
        ], $headers)
        ->assertStatus(201);

        Mail::assertNotQueued(TerrafundProgrammeInviteReceived::class, function ($mail) {
            return $mail->hasTo('a.new.user@email.com');
        });

        Mail::assertQueued(UserInvited::class, function ($mail) {
            return $mail->hasTo('a.new.user@email.com');
        });
    }

    public function testCreateActionEmailAddressIsRequired(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/terrafund/programme/1/invite', $headers)
        ->assertStatus(422);
    }

    public function testCreateActionProgrammeIdHasToExist(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/terrafund/programme/6102000/invite', [
            'email_address' => 'a.new.user@email.com',
        ], $headers)
        ->assertStatus(404);
    }

    public function testAcceptAction(): void
    {
        Carbon::setTestNow(Carbon::createFromDate('2021-07-23'));
        $token = Auth::attempt([
            'email_address' => 'new.terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'token' => 'tlvOSFc5kpR2VqrCUiwI3gabz5OeLr7LdUmhyyF693agCu7fyW9d8p4pBtEGORmj',
        ];

        $response = $this->postJson('/api/terrafund/programme/invite/accept', $data, $headers);
        $response->assertStatus(200);

        $this->assertDatabaseHas('terrafund_programme_user', [
            'user_id' => 14,
            'terrafund_programme_id' => 1,
        ]);
    }

    public function testAcceptActionRequiresEmailAndTokenToMatch(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund.orphan@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'token' => 'tlvOSFc5kpR2VqrCUiwI3gabz5OeLr7LdUmhyyF693agCu7fyW9d8p4pBtEGORmj',
        ];

        $this->postJson('/api/terrafund/programme/invite/accept', $data, $headers)
        ->assertStatus(404);
    }
}
