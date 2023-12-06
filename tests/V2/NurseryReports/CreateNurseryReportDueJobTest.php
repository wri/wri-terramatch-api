<?php

namespace Tests\Feature\SiteReports;

use App\Jobs\V2\CreateNurseryReportDueJob;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateNurseryReportDueJobTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateProjectDueSubmissions()
    {
        $nursery = Nursery::factory(['framework_key' => 'terrafund'])->create();

        CreateNurseryReportDueJob::dispatchSync('terrafund');

        $this->assertDatabaseHas(NurseryReport::class, [
            'nursery_id' => $nursery->id,
            'framework_key' => 'terrafund',
            'due_at' => Carbon::now()->addMonth()->startOfDay()->toDateTimeString(),
            'status' => NurseryReport::STATUS_DUE,
        ]);
    }
}
