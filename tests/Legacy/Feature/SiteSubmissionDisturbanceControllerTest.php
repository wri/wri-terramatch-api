<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class SiteSubmissionDisturbanceControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
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
            'site_submission_id' => 1,
            'disturbance_type' => 'manmade',
            'extent' => '61-80',
            'intensity' => 'high',
            'description' => 'description of disturbance',
        ];

        $this->postJson('/api/site/submission/disturbance', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonFragment([
                'site_submission_id' => 1,
                'disturbance_type' => 'manmade',
                'extent' => '61-80',
                'intensity' => 'high',
                'description' => 'description of disturbance',
            ]);
    }

    public function testCreateActionRequiresBeingPartOfSiteProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'site_submission_id' => 1,
            'disturbance_type' => 'manmade',
            'extent' => '61-80',
            'intensity' => 'high',
            'description' => 'description of disturbance',
        ];

        $this->postJson('/api/site/submission/disturbance', $data, $headers)
            ->assertStatus(403);
    }

    public function testCreateActionRequiresSubmissionId(): void
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
            'disturbance_type' => 'manmade',
            'extent' => '61-80',
            'intensity' => 'high',
            'description' => 'description of disturbance',
        ];

        $this->postJson('/api/site/submission/disturbance', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422);
    }

    public function testCreateActionRequiresDisturbanceType(): void
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
            'site_submission_id' => 1,
            'extent' => '61-80',
            'intensity' => 'high',
            'description' => 'description of disturbance',
        ];

        $this->postJson('/api/site/submission/disturbance', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422);
    }

    public function testCreateActionRequiresIntensity(): void
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
            'site_submission_id' => 1,
            'disturbance_type' => 'manmade',
            'extent' => '61-80',
            'description' => 'description of disturbance',
        ];

        $this->postJson('/api/site/submission/disturbance', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422);
    }

    public function testCreateActionDoesNotRequireDescription(): void
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
            'site_submission_id' => 1,
            'extent' => '61-80',
            'disturbance_type' => 'ecological',
            'intensity' => 'high',
        ];

        $this->postJson('/api/site/submission/disturbance', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonFragment([
                'site_submission_id' => 1,
                'disturbance_type' => 'ecological',
                'extent' => '61-80',
                'intensity' => 'high',
                'description' => null,
            ]);
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
            'disturbance_type' => 'manmade',
            'extent' => '61-80',
            'intensity' => 'medium',
            'description' => 'new description of disturbance',
        ];

        $this->putJson('/api/site/submission/disturbance/1', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 1,
                'site_submission_id' => 1,
                'disturbance_type' => 'manmade',
                'extent' => '61-80',
                'intensity' => 'medium',
                'description' => 'new description of disturbance',
            ]);
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

        $this->deleteJson('/api/site/submission/disturbance/1', $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200);
    }

    public function testCreateDisturbanceInformationAction(): void
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
            'site_submission_id' => 1,
            'disturbance_information' => 'this is some disturbance information',
        ];

        $this->postJson('/api/site/submission/disturbance_information', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 1,
                'disturbance_information' => 'this is some disturbance information',
            ]);
    }

    public function testCreateDisturbanceInformationActionRequiresBeingPartOfProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'site_submission_id' => 1,
            'disturbance_information' => 'this is some disturbance information',
        ];

        $this->postJson('/api/site/submission/disturbance_information', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(403);
    }

    public function testCreateDisturbanceInformationActionRequiresSiteSubmissionId(): void
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
            'disturbance_information' => 'this is some disturbance information',
        ];

        $this->postJson('/api/site/submission/disturbance_information', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422);
    }

    public function testCreateDisturbanceInformationActionRequiresDisturbanceInformation(): void
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
            'site_submission_id' => 1,
        ];

        $this->postJson('/api/site/submission/disturbance_information', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422);
    }

    public function testUpdateDisturbanceInformationAction(): void
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
            'disturbance_information' => 'this is some new disturbance information',
        ];

        $this->putJson('/api/site/submission/disturbance_information/1', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 1,
                'disturbance_information' => 'this is some new disturbance information',
            ]);
    }

    public function testDeleteDisturbanceInformationAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->deleteJson('/api/site/submission/disturbance_information/1', $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200);

        $this->assertDatabaseHas('site_submissions', [
            'id' => 1,
            'disturbance_information' => null,
        ]);
    }
}
