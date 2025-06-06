<?php

namespace App\Jobs;

use App\Mail\TaskDigestMail;
use App\Models\Traits\SkipRecipientsTrait;
use App\Models\V2\Tasks\Task;
use App\StateMachines\ReportStatusStateMachine;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDailyDigestNotificationsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use SkipRecipientsTrait;

    private $task;

    /**
     * Create a new job instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->task->project) {
            return;
        }
        $usersPdWithSkip = $this->skipRecipients($this->task->project->users()->wherePivot('is_monitoring', true)->get());
        $usersManagersWithSkip = $this->skipRecipients($this->task->project->managers()->get());
        $usersPDGroupedByLocale = $usersPdWithSkip->groupBy('locale');
        $usersManagersGroupedByLocale = $usersManagersWithSkip->groupBy('locale');
        $taskDueAt = Carbon::parse($this->task->due_at);

        if (! $this->verifyIfReportsAreApproved($this->task) && (Carbon::now()->greaterThanOrEqualTo($taskDueAt->subWeek()) || Carbon::now()->greaterThan($taskDueAt))) {
            foreach ($usersPDGroupedByLocale as $locale => $users) {
                $groupedLocale['locale'] = $locale;
                Mail::to($users->pluck('email_address')->toArray())->queue(new TaskDigestMail($groupedLocale, $this->task, false));
            }
            foreach ($usersManagersGroupedByLocale as $locale => $users) {
                $groupedLocaleManager['locale'] = $locale;
                Mail::to($users->pluck('email_address')->toArray())->queue(new TaskDigestMail($groupedLocaleManager, $this->task, true));
            }
        }
    }

    public function verifyIfReportsAreApproved($task)
    {
        $allReports = collect([
            $task->projectReport()->get(),
            $task->siteReports()->get(),
            $task->nurseryReports()->get(),
        ])->flatten(1);
        $completedList = $allReports->filter(function ($report) {
            return $report['status'] === ReportStatusStateMachine::APPROVED;
        });
        $allReportsApproved = count($allReports) == count($completedList);

        return $allReportsApproved;
    }
}
