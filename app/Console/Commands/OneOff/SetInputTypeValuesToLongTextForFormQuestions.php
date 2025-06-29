<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Forms\FormQuestion;
use Illuminate\Console\Command;

class SetInputTypeValuesToLongTextForFormQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:set-default-conditional-values-to-long-text-for-form-questions';

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
            'org-decisionmaking-structure-description', 'org-decisionmaking-structure-individuals-involved', 'org-bioeconomy-traditional-knowledge', 'org-bioeconomy-product-processing',
        ])->each(function (FormQuestion $formQuestion): void {
            $formQuestion->update(['input_type' => 'long-text', 'min_character_limit' => 0, 'max_character_limit' => 90000]);
        });
    }
}
