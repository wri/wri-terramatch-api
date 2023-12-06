<?php

namespace Tests\Legacy\Feature\Terrafund;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\Legacy\LegacyTestCase;

final class LegacyTerrafundDueSubmissionControllerTest extends LegacyTestCase
{
    public function testReadAllDueSiteSubmissionsForUserAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/site/submission/due', $headers)
             ->assertHeader('Content-Type', 'application/json')
             ->assertStatus(200)
             ->assertJsonCount(2, 'data')
             ->assertJsonPath('data.0.id', 1);
    }

    public function testReadAllDueNurserySubmissionsForUserAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/nursery/submission/due', $headers)
             ->assertHeader('Content-Type', 'application/json')
             ->assertStatus(200)
             ->assertJsonCount(1, 'data');
    }

    public function testReadAllPastSiteSubmissionsForUserAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/site/submissions/submitted', $headers)
             ->assertHeader('Content-Type', 'application/json')
             ->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.id', 5);
    }

    public function testReadAllPastNurserySubmissionsForUserAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/nursery/submissions/submitted', $headers)
             ->assertHeader('Content-Type', 'application/json')
             ->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.id', 4);
    }

    public function testUnableToReportOnDueSubmissionAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = ['reason' => 'lorem ipsum'];
        $this->postJson('/api/terrafund/submission/2/unable', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonPath('data.unable_report_reason', 'lorem ipsum')
            ->assertJsonPath('data.is_submitted', 1);

        $this->assertDatabaseHas('terrafund_due_submissions', [
            'id' => 2,
            'unable_report_reason' => 'lorem ipsum',
            'is_submitted' => true,
        ]);

        $this->assertDatabaseHas('terrafund_due_submissions', [
            'id' => 1,
            'unable_report_reason' => 'lorem ipsum',
            'is_submitted' => true,
        ]);

        $this->assertDatabaseHas('terrafund_due_submissions', [
            'id' => 3,
            'unable_report_reason' => 'lorem ipsum',
            'is_submitted' => true,
        ]);
    }

    public function testUnableToReportOnDueSubmissionActionOnLimit(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = ['reason' => Str::random(65000)];
        $this->postJson('/api/terrafund/submission/2/unable', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonPath('data.unable_report_reason', $data['reason'])
            ->assertJsonPath('data.is_submitted', 1);
    }

    public function testUnableToReportOnDueSubmissionActionNotString(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = ['reason' => 0.00000];
        $this->postJson('/api/terrafund/submission/2/unable', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422);
    }

    public function testUnableToReportOnDueSubmissionActionTooLong(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = ['reason' => Str::random(65001)];
        $this->postJson('/api/terrafund/submission/2/unable', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422);
    }

    public function testUnableToReportOnDueSubmissionActionNotSupplied(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [];
        $this->postJson('/api/terrafund/submission/2/unable', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422);
    }
}
