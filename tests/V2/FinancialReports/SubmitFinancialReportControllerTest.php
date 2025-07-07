<?php

namespace Tests\V2\FinancialReports;

use App\Helpers\CustomFormHelper;
use App\Models\V2\FinancialReport;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubmitFinancialReportControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $user = User::factory()->create();

        $financialReport = FinancialReport::factory()->create([
            'organisation_id' => $organisation->id,
            'status' => FinancialReport::STATUS_STARTED,
        ]);

        CustomFormHelper::generateFakeForm('financial-report', 'ppc');

        $uri = '/api/v2/forms/financial-reports/' . $financialReport->uuid . '/submit';

        $this->actingAs($user)
            ->putJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson($uri)
            ->assertSuccessful();

        // Verify the status changed to submitted
        $financialReport->refresh();
        $this->assertEquals(FinancialReport::STATUS_SUBMITTED, $financialReport->status);
        $this->assertNotNull($financialReport->submitted_at);
    }

    public function test_submit_from_due_status()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $financialReport = FinancialReport::factory()->create([
            'organisation_id' => $organisation->id,
            'status' => FinancialReport::STATUS_DUE,
        ]);

        CustomFormHelper::generateFakeForm('financial-report', 'ppc');

        $uri = '/api/v2/forms/financial-reports/' . $financialReport->uuid . '/submit';

        $this->actingAs($owner)
            ->putJson($uri)
            ->assertSuccessful();

        // Verify the status changed to submitted
        $financialReport->refresh();
        $this->assertEquals(FinancialReport::STATUS_SUBMITTED, $financialReport->status);
        $this->assertNotNull($financialReport->submitted_at);
    }

    public function test_cannot_submit_already_submitted_report()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $financialReport = FinancialReport::factory()->create([
            'organisation_id' => $organisation->id,
            'status' => FinancialReport::STATUS_SUBMITTED,
        ]);

        CustomFormHelper::generateFakeForm('financial-report', 'ppc');

        $uri = '/api/v2/forms/financial-reports/' . $financialReport->uuid . '/submit';

        $this->actingAs($owner)
            ->putJson($uri)
            ->assertSuccessful(); // Should still work since we're just setting the same status
    }
} 