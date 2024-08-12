<?php

namespace Tests\Feature\Terrafund;

use App\Models\Draft;
use App\Models\Organisation;
use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TerrafundSubmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function testProgrammeSubmissionFlow(): void
    {
        $organisation = Organisation::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);

        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        $dueSubmission = TerrafundDueSubmission::factory()->create([
            'terrafund_due_submissionable_type' => TerrafundProgramme::class,
            'terrafund_due_submissionable_id' => $terrafundProgramme->id,
        ]);

        $draft = Draft::factory()->terrafundProgrammeSubmission()->create([
            'created_by' => $user->id,
            'organisation_id' => $user->organisation_id,
            'terrafund_due_submission_id' => $dueSubmission->id,
            'data' => json_encode([
                'terrafund_programme_submission' => TerrafundProgrammeSubmission::factory()->make(['terrafund_programme_id' => $terrafundProgramme->id]),
                'photos' => [],
                'other_additional_documents' => [],
            ]),
        ]);

        $response = $this->actingAs($user)
            ->patchJson("/api/drafts/$draft->id/publish")
            ->assertStatus(201);
    }

    public function testSiteSubmissionFlow(): void
    {
        $organisation = Organisation::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);

        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        $terrafundSite = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);

        $dueSubmission = TerrafundDueSubmission::factory()->create([
            'terrafund_due_submissionable_type' => TerrafundSite::class,
            'terrafund_due_submissionable_id' => $terrafundSite->id,
        ]);

        $draft = Draft::factory()->terrafundSiteSubmission()->create([
            'created_by' => $user->id,
            'organisation_id' => $user->organisation_id,
            'terrafund_due_submission_id' => $dueSubmission->id,
            'data' => json_encode([
                'terrafund_site_submission' => TerrafundSiteSubmission::factory()->make(['terrafund_site_id' => $terrafundSite->id, 'terrafund_due_submission_id' => $dueSubmission->id]),
                'photos' => [],
                'tree_species' => [],
                'non_tree_species' => [],
                'disturbances' => [],
            ]),
        ]);

        $response = $this->actingAs($user)
            ->patchJson("/api/drafts/$draft->id/publish")
            ->assertStatus(201);
    }

    public function testNurserySubmissionFlow(): void
    {
        $organisation = Organisation::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);

        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        $terrafundNursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);

        $dueSubmission = TerrafundDueSubmission::factory()->create([
            'terrafund_due_submissionable_type' => TerrafundNursery::class,
            'terrafund_due_submissionable_id' => $terrafundNursery->id,
        ]);

        $draft = Draft::factory()->terrafundNurserySubmission()->create([
            'created_by' => $user->id,
            'organisation_id' => $user->organisation_id,
            'terrafund_due_submission_id' => $dueSubmission->id,
            'data' => json_encode([
                'terrafund_nursery_submission' => TerrafundNurserySubmission::factory()->make(['terrafund_nursery_id' => $terrafundNursery->id]),
                'photos' => [],
            ]),
        ]);

        $response = $this->actingAs($user)
            ->patchJson("/api/drafts/$draft->id/publish")
            ->assertStatus(201);
    }
}
