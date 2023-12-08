<?php

namespace App\Jobs\V2;

use App\Mail\TerrafundSiteAndNurseryReminder;
use App\Models\V2\Projects\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSiteAndNurseryRemindersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $frameworkKey;

    public function __construct(string $frameworkKey)
    {
        $this->frameworkKey = $frameworkKey;
    }

    public function handle()
    {
        Project::query()
            ->where('framework_key', '=', $this->frameworkKey)
            ->whereDoesntHave('sites')
            ->whereDoesntHave('nurseries')
            ->chunkById(100, function ($projects) {
                $projects->each(function ($project) {
                    if ($project->users->count()) {
                        Mail::to($project->users->pluck('email_address'))->queue(new TerrafundSiteAndNurseryReminder($project->id));
                    }
                });
            });
    }
}
