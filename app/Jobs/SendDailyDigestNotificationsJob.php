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
        foreach ($users as $user) {
            Mail::to($user->email_address)->send(
                new TaskDigestMail($user, $this->task)
            );
        }
    }
}
