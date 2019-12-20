<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        "App\\Console\\Commands\\MigrateServicesCommand",
        "App\\Console\\Commands\\RemovePasswordResetsCommand",
        "App\\Console\\Commands\\RemoveVerificationsCommand",
        "App\\Console\\Commands\\RemoveUploadsCommand",
        "App\\Console\\Commands\\CreateAdminCommand",
        "App\\Console\\Commands\\FindMatchesCommand",
        "App\\Console\\Commands\\RemoveNotificationsCommand"
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command("remove-verifications")->everyMinute();
        $schedule->command("remove-password-resets")->everyMinute();
        $schedule->command("remove-uploads")->everyMinute();
        $schedule->command("find-matches")->everyMinute();
        $schedule->command("remove-notifications")->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
