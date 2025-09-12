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
    protected $signature = 'one-off:delete-form-questions-by-linked-field-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes form questions with specific linked_field_keys';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        FormQuestion::whereIn('linked_field_key', [
            'org-fin-bgt-cur-year',
            'org-fin-bgt-1year',
            'org-fin-bgt-2year',
            'org-fin-bgt-3year',
        ])->delete();
    }
}
