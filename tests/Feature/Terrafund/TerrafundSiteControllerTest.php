<?php

namespace Tests\Feature\Terrafund;

use App\Models\Framework;
use App\Models\Organisation;
use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

final class TerrafundSiteControllerTest extends TestCase
{
    public function testReadMySitesAction(): void
    {
        Carbon::setTestNow(Carbon::create(2021, 07, 28));
        $organisation = Organisation::factory()->create();
        $terrafundFramework = Framework::factory()->create([
            'name' => 'Terrafund',
        ]);
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $terrafundSite = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $missingSite = TerrafundSite::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $user->frameworks()->attach($terrafundFramework->id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        Carbon::setTestNow();

        $dueSubmission = TerrafundDueSubmission::factory()->create([
            'terrafund_due_submissionable_type' => TerrafundSite::class,
            'terrafund_due_submissionable_id' => $terrafundSite->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/terrafund/my/sites')
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $terrafundSite->id])
            ->assertJsonMissingExact(['id' => $missingSite->id])
            ->assertJsonPath('data.0.next_due_submission_id',  $dueSubmission->id)
            ->assertJsonPath('data.0.next_due_submission_due_at',   $dueSubmission->due_at->toISOString());
    }
}
