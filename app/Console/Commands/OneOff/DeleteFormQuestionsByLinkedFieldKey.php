<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Forms\FormQuestion;
use Illuminate\Console\Command;

class DeleteFormQuestionsByLinkedFieldKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:delete-form-questions-by-linked-field-key {linkedFieldKeys*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft delete form questions by linked field keys';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $linkedFieldKeys = $this->argument('linkedFieldKeys');

        $this->info('Looking for form questions to delete: ' . json_encode($linkedFieldKeys, JSON_PRETTY_PRINT));

        $questions = FormQuestion::whereIn('linked_field_key', $linkedFieldKeys)
            ->get();

        $this->info("Found {$questions->count()} form questions to delete");

        $questions->each(function ($question) {
            $this->info("Soft deleting form question: [{$question->uuid}, {$question->linked_field_key}]");
            $question->delete();
        });

        $this->info('Form questions soft deleted successfully!');
    }
}
