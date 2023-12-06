<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class SocioeconomicBenefitsControllerTest extends LegacyTestCase
{
    public function testUploadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'upload' => 8,
            'name' => 'test benefit',
            'programme_id' => 1,
        ];

        $response = $this->postJson('/api/uploads/socioeconomic_benefits', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
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
        $data = [
            'upload' => 41,
            'name' => 'new test benefit',
            'site_submission_id' => 1,
            'site_id' => 1,
        ];

        $response = $this->patchJson('/api/uploads/socioeconomic_benefits', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);

        $this->assertDatabaseMissing('socioeconomic_benefits', [
            'site_submission_id' => 1,
            'name' => 'test benefit',
        ]);

        $this->assertDatabaseHas('socioeconomic_benefits', [
            'site_submission_id' => 1,
            'name' => 'new test benefit',
        ]);
    }

    public function testUpdateActionWhenNoFileCurrentlyExists(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'upload' => 40,
            'name' => 'new test benefit',
            'site_submission_id' => 3,
            'site_id' => 1,
        ];

        $this->assertDatabaseMissing('socioeconomic_benefits', [
            'site_submission_id' => 3,
        ]);

        $response = $this->patchJson('/api/uploads/socioeconomic_benefits', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);

        $this->assertDatabaseHas('socioeconomic_benefits', [
            'site_submission_id' => 3,
            'name' => 'new test benefit',
        ]);
    }

    public function testDownloadTemplateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/uploads/socioeconomic_benefits/template', $headers)
            ->assertStatus(200);
    }

    public function testDownloadSiteSubmissionTemplateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/uploads/socioeconomic_benefits/template/site_submission', $headers)
            ->assertStatus(200);
    }

    public function testDownloadProgrammeSubmissionTemplateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/uploads/socioeconomic_benefits/template/programme_submission', $headers)
            ->assertStatus(200);
    }

    public function testDownloadCsvTemplateAction(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/uploads/socioeconomic_benefits/template/csv', $headers)
            ->assertStatus(200);
    }
}
