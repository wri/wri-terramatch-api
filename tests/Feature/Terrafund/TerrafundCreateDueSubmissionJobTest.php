<?php

namespace Tests\Feature\Terrafund;

use App\Jobs\Terrafund\TerrafundCreateNurseryDueSubmissionJob;
use App\Jobs\Terrafund\TerrafundCreateSiteDueSubmissionJob;
use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundSite;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TerrafundCreateDueSubmissionJobTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateNurseryDueSubmissions(): void
    {
        $nursery = TerrafundNursery::factory()->create();
        TerrafundCreateNurseryDueSubmissionJob::dispatchSync();
        //This is needed because the first due submission is created outside the cycle at creation
        TerrafundCreateNurseryDueSubmissionJob::dispatchSync();
        $this->assertDatabaseHas(TerrafundDueSubmission::class, [
            'terrafund_due_submissionable_id' => $nursery->id,
            'terrafund_due_submissionable_type' => TerrafundNursery::class,
            'due_at' => Carbon::now()->addMonth()->startOfDay()->toDateTimeString(),
        ]);
    }

    public function testCreateSiteDueSubmissions(): void
    {
        $site = TerrafundSite::factory()->create();
        TerrafundCreateSiteDueSubmissionJob::dispatchSync();
        //This is needed because the first due submission is created outside the cycle at creation
        TerrafundCreateSiteDueSubmissionJob::dispatchSync();
        $this->assertDatabaseHas(TerrafundDueSubmission::class, [
            'terrafund_due_submissionable_id' => $site->id,
            'terrafund_due_submissionable_type' => TerrafundSite::class,
            'due_at' => Carbon::now()->addMonth()->startOfDay()->toDateTimeString(),
        ]);
    }
}
