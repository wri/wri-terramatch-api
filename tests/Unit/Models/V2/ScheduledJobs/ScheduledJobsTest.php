<?php

namespace Tests\Unit\Models\V2\ScheduledJobs;

use App\Jobs\V2\NotifyReportReminderJob;
use App\Mail\TerrafundReportReminder;
use App\Mail\TerrafundSiteAndNurseryReminder;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Models\V2\ScheduledJobs\ReportReminderJob;
use App\Models\V2\ScheduledJobs\ScheduledJob;
use App\Models\V2\ScheduledJobs\SiteAndNurseryReminderJob;
use App\Models\V2\ScheduledJobs\TaskDueJob;
use App\Models\V2\Sites\Site;
use App\Models\V2\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ScheduledJobsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_sti()
    {
        TaskDueJob::createTaskDue(Carbon::now()->addYear(), 'ppc', Carbon::now()->addYear());
        ReportReminderJob::createReportReminder(Carbon::now()->subDay(), 'terrafund');
        SiteAndNurseryReminderJob::createSiteAndNurseryReminder(Carbon::now()->addMinute(), 'terrafund');

        $this->assertEquals(3, ScheduledJob::count());
        $this->assertEquals(1, ScheduledJob::readyToExecute()->count());
        $this->assertEquals(
            [TaskDueJob::class, ReportReminderJob::class, SiteAndNurseryReminderJob::class],
            ScheduledJob::pluck('type')->values()->toArray()
        );
    }

    public function test_task_due()
    {
        $dueAt = Carbon::now()->startOfDay()->addMonth();
        $project = Project::factory()->terrafund()->create();
        $site = Site::factory()->terrafund()->create(['project_id' => $project->id]);
        $nursery = Nursery::factory()->terrafund()->create(['project_id' => $project->id]);
        TaskDueJob::createTaskDue(Carbon::now()->subDay(), 'terrafund', $dueAt);

        $this->assertEquals(0, $project->tasks()->count());

        ScheduledJob::readyToExecute()->first()->execute();

        $this->assertEquals(1, $project->tasks()->count());
        $this->assertEquals($dueAt, $project->tasks()->first()->due_at);
        $this->assertEquals(1, $project->reports()->count());
        $this->assertEquals($dueAt, $project->reports()->first()->due_at);
        $this->assertEquals(1, $site->reports()->count());
        $this->assertEquals($dueAt, $site->reports()->first()->due_at);
        $this->assertEquals(1, $nursery->reports()->count());
        $this->assertEquals($dueAt, $nursery->reports()->first()->due_at);
    }

    public function test_report_reminder()
    {
        Mail::fake();
        Queue::fake();
        $user = User::factory()->create(['locale' => 'en-US']);
        $project = Project::factory()->terrafund()->create();
        $user->projects()->sync([$project->id => ['is_monitoring' => true]]);
        Site::factory()->terrafund()->create(['project_id' => $project->id]);
        ReportReminderJob::createReportReminder(Carbon::now()->subDay(), 'terrafund');

        ScheduledJob::readyToExecute()->first()->execute();

        Mail::assertQueued(TerrafundReportReminder::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email_address);
        });
        Queue::assertPushed(NotifyReportReminderJob::class, 1);
    }

    public function test_site_and_nursery_reminder()
    {
        Mail::fake();
        $user = User::factory()->create(['locale' => 'en-US']);
        $project = Project::factory()->terrafund()->create();
        $user->projects()->sync([$project->id => ['is_monitoring' => true]]);
        SiteAndNurseryReminderJob::createSiteAndNurseryReminder(Carbon::now()->subDay(), 'terrafund');

        ScheduledJob::readyToExecute()->first()->execute();

        Mail::assertQueued(TerrafundSiteAndNurseryReminder::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email_address);
        });
    }
}
