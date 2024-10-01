<?php

namespace App\Console\Commands;

use App\Jobs\SendDailyDigestNotificationsJob;
use App\Models\V2\Tasks\Task;
use Illuminate\Console\Command;

class SendDailyDigestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-daily-digest-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs daily digest notifications for tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Task::isIncomplete()->chunkById(100, function ($tasks) {
            $tasks->each(function (Task $task) {
                SendDailyDigestNotificationsJob::dispatchSync($task);
            });
        });
    }
}
