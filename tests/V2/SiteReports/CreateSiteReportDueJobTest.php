<?php

namespace Tests\Feature\SiteReports;

use App\Jobs\V2\CreateSiteReportDueJob;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateSiteReportDueJobTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateProjectDueSubmissions()
    {
        $site = Site::factory(['framework_key' => 'terrafund'])->create();

        CreateSiteReportDueJob::dispatchSync('terrafund');

        $this->assertDatabaseHas(SiteReport::class, [
            'site_id' => $site->id,
            'framework_key' => 'terrafund',
            'due_at' => Carbon::now()->addMonth()->startOfDay()->toDateTimeString(),
            'status' => SiteReport::STATUS_DUE,
        ]);
    }
}
