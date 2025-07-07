<?php

namespace Tests\V2\FinancialReports;

use App\Helpers\CustomFormHelper;
use App\Models\V2\FinancialReport;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateFinancialReportWithFormControllerTest extends TestCase
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

        $form = CustomFormHelper::generateFakeForm('financial-report', 'ppc', true);

        $answers = [];
        foreach ($form->sections()->first()->questions as $question) {
            if ($question->linked_field_key == 'fin-rep-title') {
                $answers[$question->uuid] = '* testing title updated *';
            }
        }

        $payload = ['answers' => $answers];
        $uri = '/api/v2/forms/financial-reports/' . $financialReport->uuid;

        $this->actingAs($user)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson($uri, $payload)
            ->assertSuccessful();

        $updated = $financialReport->fresh();
        $this->assertEquals($updated->title, '* testing title updated *');
    }

    public function test_admin_can_update_submitted_report()
    {
        $organisation = Organisation::factory()->create();
        $admin = User::factory()->ppcAdmin()->create();

        $financialReport = FinancialReport::factory()->create([
            'organisation_id' => $organisation->id,
            'status' => FinancialReport::STATUS_SUBMITTED,
        ]);

        $form = CustomFormHelper::generateFakeForm('financial-report', 'ppc', true);

        $answers = [];
        foreach ($form->sections()->first()->questions as $question) {
            if ($question->linked_field_key == 'fin-rep-title') {
                $answers[$question->uuid] = '* testing title updated by admin *';
            }
        }

        $payload = ['answers' => $answers];
        $uri = '/api/v2/forms/financial-reports/' . $financialReport->uuid;

        $this->actingAs($admin)
            ->putJson($uri, $payload)
            ->assertSuccessful();

        $updated = $financialReport->fresh();
        $this->assertEquals($updated->title, '* testing title updated by admin *');
    }
}
