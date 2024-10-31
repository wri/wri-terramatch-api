<?php

namespace App\Jobs;

use App\Mail\TaskDigestMail;
use App\Models\V2\Tasks\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\StateMachines\ReportStatusStateMachine;

class SendDailyDigestNotificationsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        $users = $this->task->project->users()->get();
        $usersGroupedByLocale = $users->groupBy('locale');

        if (!$this->verifyIfReportsAreApproved($this->task)) {
            foreach ($usersGroupedByLocale as $locale => $users) {
                $groupedLocale['locale'] = $locale;
                Mail::to($users->pluck('email_address')->toArray())->queue(new TaskDigestMail($groupedLocale, $this->task));
            }
        }
    }
    public function verifyIfReportsAreApproved ($task) {
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
