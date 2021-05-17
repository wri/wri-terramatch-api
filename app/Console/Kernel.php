<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command("remove-verifications")->everyFiveMinutes();
        $schedule->command("remove-password-resets")->everyFiveMinutes();
        $schedule->command("remove-uploads")->everyFiveMinutes();
        $schedule->command("remove-elevator-videos")->everyFiveMinutes();
        $schedule->command("remove-notifications")->everyFiveMinutes();
        $schedule->command("remove-notifications-buffers")->everyFiveMinutes();
        $schedule->command("remove-filter-records")->everyFiveMinutes();
        $schedule->command("find-matches")->everyMinute();
        $schedule->command("create-visibility-notifications")->everyFiveMinutes();
        $schedule->command("check-queue-length")->everyFiveMinutes();
        $schedule->command("send-upcoming-progress-update-notifications")->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
