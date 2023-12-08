<?php

namespace App\Console\Commands;

use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\Stages\Stage;
use Illuminate\Console\Command;

class V2CustomFormPrepPhase2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-custom-form-prep-phase2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post data setup for ready for phase 2';

    public function handle()
    {
        $stages = Stage::all();
        foreach ($stages as $stage) {
            if (empty($stage->name)) {
                $stage->update([
                    'name' => 'Expression of Interest',
                    'order' => 1,
                    'status' => Stage::STATUS_ACTIVE,
                ]);
            }
        }

        $submissions = FormSubmission::all();
        foreach ($submissions as $submission) {
            if (empty($submission->application_id)) {
                if (! empty($submission->form) && ! empty($submission->form->stage)) {
                    $application = Application::create([
                        'organisation_uuid' => $submission->organisation_uuid,
                        'funding_programme_uuid' => data_get($submission->form->stage, 'funding_programme_id'),
                    ]);

                    $submission->update([
                        'application_id' => $application->id,
                        'stage_uuid' => $submission->form->stage_id,
                    ]);
                }
            }
        }
    }
}
