<?php

namespace Tests\Unit\Jobs;

use App\Jobs\Terrafund\TerrafundCreateProgrammeDueSubmissionJob;
use App\Models\Terrafund\TerrafundProgramme;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TerrafundCreateProgrammeDueSubmissionJobTest extends TestCase
{
    use RefreshDatabase;

    public function testJobCreatesProgrammeDueSubmissions(): void
    {
        Carbon::setTestNow('2021-08-07 10:30:00');
        $programme = TerrafundProgramme::factory()->create();
        TerrafundCreateProgrammeDueSubmissionJob::dispatchSync();
        //This is needed because the first due submission is created outside the cycle at creation
        TerrafundCreateProgrammeDueSubmissionJob::dispatchSync();

        $this->assertDatabaseHas('terrafund_due_submissions', [
            'terrafund_due_submissionable_type' => TerrafundProgramme::class,
            'terrafund_due_submissionable_id' => $programme->id,
            'due_at' => '2021-09-07 00:00:00',
            'is_submitted' => false,
            'unable_report_reason' => null,
        ]);
    }

    public function testJobBatchesProgrammeDueSubmissions(): void
    {
        Carbon::setTestNow('2021-08-07 10:30:00');
        TerrafundProgramme::factory()->count(182)->create();
        TerrafundCreateProgrammeDueSubmissionJob::dispatchSync();

        $this->assertDatabaseCount('terrafund_due_submissions', 182);
    }
}
