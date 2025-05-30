<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Forms\FormQuestion;
use Illuminate\Console\Command;

class SetInputTypeValuesForFormQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:set-default-conditional-values-for-form-questions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the input_type to long-text for selected form questions with specific linked_field_keys';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        FormQuestion::whereIn('linked_field_key', [
            'org-consortium', 'org-female-youth-leadership-example', 'org-external-technical-assistance', 'org-barriers-to-funding', 'org-capacity-building-support-needed',
            'pro-pit-landowner-agreement-description', 'pro-pit-land-tenure-risks', 'pro-pit-non-tree-interventions-description', 'pro-pit-complement-existing-restoration',
        ])->each(function (FormQuestion $formQuestion): void {
            $formQuestion->update(['input_type' => 'long-text']);
        });
    }
}
