<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;

class UpdateProjectDemographicsType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update-project-demographics-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changes employees demographics to jobs under projects and pitches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Demographic::withTrashed()
            ->where('type', Demographic::EMPLOYEES_TYPE)
            ->whereIn('demographical_type', [Project::class, ProjectPitch::class])
            ->update(['type' => Demographic::JOBS_TYPE]);

        FormQuestion::withTrashed()
            ->whereIn('linked_field_key', ['pro-pit-all-jobs', 'pro-all-jobs'])
            ->update(['input_type' => 'jobs']);

    }
}
