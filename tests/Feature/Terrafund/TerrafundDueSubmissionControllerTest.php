<?php

namespace Tests\Feature\Terrafund;

use App\Models\Organisation;
use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TerrafundDueSubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testReadAllDueSubmissionsForUserAction(): void
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

        TerrafundDueSubmission::factory()->count(3)->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
            'terrafund_due_submissionable_type' => TerrafundSite::class,
            'terrafund_due_submissionable_id' => $terrafundSite->id,
        ]);

        $dueSubmissions = TerrafundDueSubmission::factory()->count(2)->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
            'terrafund_due_submissionable_type' => TerrafundProgramme::class,
            'terrafund_due_submissionable_id' => $terrafundProgramme->id,
        ]);

        $dueSubmission = $dueSubmissions->first();
        $this->assertNull($dueSubmission->submitted_at);
        $dueSubmission->update(['is_submitted' => true]);
        $this->assertInstanceOf(Carbon::class, $dueSubmission->submitted_at);

        //        TerrafundDueSubmission::factory()->count(1)->create([
        //            'terrafund_programme_id' => $terrafundProgramme->id,
        //            'terrafund_due_submissionable_type' => TerrafundProgramme::class,
        //            'terrafund_due_submissionable_id' => $terrafundProgramme->id,
        //        ]);

        $nextDueSubmission = TerrafundDueSubmission::unsubmitted()
//          ->where('terrafund_programme_id', $terrafundProgramme->id)
            ->where('terrafund_due_submissionable_type', TerrafundProgramme::class)
            ->where('terrafund_due_submissionable_id', $terrafundProgramme->id)
            ->orderBy('due_at')
            ->first();

        $response = $this->actingAs($user)
            ->getJson('/api/terrafund/submissions/due')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.terrafund_programme_id', $terrafundProgramme->id)
            ->assertJsonPath('data.0.terrafund_programme_name', $terrafundProgramme->name)
            ->assertJsonPath('data.0.terrafund_programme_has_sites', true)
            ->assertJsonPath('data.0.terrafund_programme_has_nurseries', false)
            ->assertJsonPath('data.0.terrafund_last_submission_creation_date',  $dueSubmission->submitted_at->format('Y-m-d H:i:s'))
            ->assertJsonPath('data.0.terrafund_next_submission_due_date',   $nextDueSubmission->due_at->toISOString())
            ->assertJsonCount(4, 'data.0.terrafund_due_submissions');
    }
}
