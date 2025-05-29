<?php

namespace App\Console\Commands;

use App\Models\V2\Tasks\Task;
use Illuminate\Console\Command;
use App\Mail\TerrafundReportReminder;
use Illuminate\Support\Facades\Mail;
use App\Models\Traits\SkipRecipientsTrait;
use Illuminate\Support\Facades\Log;

class CreateProjectReportReminder extends Command
{
    use SkipRecipientsTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-report-reminder {--after=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a report reminder for those tasks created after parameter';

    public function handle(): int
    {
        $after = $this->option('after');

        Task::where('created_at', '>=', $after)
            ->whereHas('project', function ($qry) {
                $qry->whereIn('framework_key', ['terrafund', 'terrafund-landscapes', 'enterprises']);
            })
            ->chunk(100, function ($tasks) {
                $tasks->each(function ($task) {
                    $project = $task->project;
                    Log::info($project->name);
                    // $usersPdWithSkip = $this->skipRecipients($project->users()->wherePivot('is_monitoring', true)->get());

                    // foreach ($usersPdWithSkip as $user) {
                    //     Mail::to($user->email_address)
                    //         ->queue(new TerrafundReportReminder($project->id, $user, $task->due_at));
                    // }
                });
            });

        return 0;
    }
}
