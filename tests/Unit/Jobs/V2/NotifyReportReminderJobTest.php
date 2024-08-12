<?php

namespace Tests\Unit\Jobs\V2;

use App\Jobs\V2\NotifyReportReminderJob;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifyReportReminderJobTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $project;

    public function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();

        $this->user = User::factory()
            ->terrafundAdmin()
            ->has(Project::factory()->terrafund())
            ->create(['organisation_id' => $organisation->id]);

        $this->project = $this->user->projects()->first();
    }

    public function test_job_creates_notification()
    {
        NotifyReportReminderJob::dispatchSync($this->user, $this->project, 'terrafund');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'title' => 'Terrafund Report Reminder',
            'body' => 'Terrafund reports are due in a month',
            'action' => 'terrafund_report_reminder',
            'referenced_model' => 'Project',
            'referenced_model_id' => $this->project->id,
            'hidden_from_app' => 1,
        ]);
    }
}
