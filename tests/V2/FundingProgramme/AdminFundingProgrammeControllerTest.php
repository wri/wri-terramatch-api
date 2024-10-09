<?php

namespace Tests\V2\FundingProgramme;

use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFundingProgrammeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexAction()
    {
        $count = FundingProgramme::count();
        $user = User::factory()->admin()->create();
        FundingProgramme::factory()->count(3)->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/funding-programme')
            ->assertStatus(200)
            ->assertJsonCount(3 + $count, 'data');
    }

    public function testIndexActionCannotBePerformedByNonAdmin()
    {
        $user = User::factory()->create();
        FundingProgramme::factory()->count(3)->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/funding-programme')
            ->assertStatus(403);
    }

    public function testStoreAction()
    {
        $user = User::factory()->admin()->create();

        $payload = [
            'name' => 'funding programme',
            'description' => 'description',
            'status' => 'active',
            'read_more_url' => 'https://this.link/',
            'location' => 'USA',
            'organisation_types' => [
                'for-profit-organization',
            ],
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/admin/funding-programme', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'funding programme',
                'description' => 'description',
                'status' => 'active',
                'read_more_url' => 'https://this.link/',
                'location' => 'USA',
                'deadline_at' => null,
                'organisation_types' => [
                    'for-profit-organization',
                ],
            ]);
    }

    public function testStoreActionCannotBePerformedByNonAdmin()
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'funding programme',
            'description' => 'description',
            'status' => 'active',
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/admin/funding-programme', $payload)
            ->assertStatus(403);
    }

    public function testShowAction()
    {
        $user = User::factory()->admin()->create();
        $form = Form::factory()->create();
        $fundingProgramme = $form->stage->fundingProgramme;

        $this->actingAs($user)
            ->getJson('/api/v2/admin/funding-programme/' . $fundingProgramme->uuid)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $fundingProgramme->id,
                'uuid' => $fundingProgramme->uuid,
                'name' => $fundingProgramme->name,
                'description' => $fundingProgramme->description,
                'read_more_url' => $fundingProgramme->read_more_url,
                'location' => $fundingProgramme->location,
                'status' => $fundingProgramme->status,
                'deleted_at' => $fundingProgramme->deleted_at,
                'created_at' => $fundingProgramme->created_at,
                'updated_at' => $fundingProgramme->updated_at,
            ]);
    }

    public function testShowActionCannotBePerformedByNonAdmin()
    {
        $user = User::factory()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/funding-programme/' . $fundingProgramme->uuid)
            ->assertStatus(403);
    }

    public function testUpdateAction()
    {
        $user = User::factory()->admin()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $payload = [
            'name' => 'new name',
            'read_more_url' => 'https://this.link/',
            'location' => 'Bosnia',
            'organisation_types' => [
                'non-profit-organization',
                'government-agency',
            ],
        ];

        $this->actingAs($user)
            ->putJson('/api/v2/admin/funding-programme/' . $fundingProgramme->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'new name',
                'read_more_url' => 'https://this.link/',
                'location' => 'Bosnia',
                'organisation_types' => [
                    'non-profit-organization',
                    'government-agency',
                ],
            ]);
    }

    public function testUpdateActionCannotBePerformedByNonAdmin()
    {
        $user = User::factory()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $payload = [
            'name' => 'new name',
        ];

        $this->actingAs($user)
            ->putJson('/api/v2/admin/funding-programme/' . $fundingProgramme->uuid, $payload)
            ->assertStatus(403);
    }

    public function testDeleteAction()
    {
        $count = FundingProgramme::count();
        $user = User::factory()->admin()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $this->assertDatabaseCount('funding_programmes', 1 + $count);

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/funding-programme/' . $fundingProgramme->uuid)
            ->assertStatus(202);

        $fundingProgramme->refresh();
        $this->assertNotNull($fundingProgramme->deleted_at);
    }

    public function testDeleteActionCannotBePerformedByNonAdmin()
    {
        $count = FundingProgramme::count();
        $user = User::factory()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $this->assertDatabaseCount('funding_programmes', 1 + $count);

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/funding-programme/' . $fundingProgramme->uuid)
            ->assertStatus(403);

        $this->assertDatabaseCount('funding_programmes', 1 + $count);
    }

    public function testOrganisationsViaFormSubmissions()
    {
        $admin = User::factory()->admin()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $organisations = Organisation::factory()->count(5)->create();
        $form = Form::factory()->create();

        foreach ($organisations as $organisation) {
            $application = Application::factory()->create([
                'organisation_uuid' => $organisation->uuid,
                'funding_programme_uuid' => $fundingProgramme->uuid,
            ]);
            FormSubmission::factory()->create([
                'form_id' => $form->uuid,
                'application_id' => $application->id,
            ]);
        }

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/funding-programme/' . $fundingProgramme->uuid)
            ->assertSuccessful()
            ->assertJsonCount(5, 'data.organisations');
    }
}
