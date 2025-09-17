<?php

namespace App\Console\Commands\OneOff;

use App\Helpers\I18nHelper;
use App\Models\V2\Forms\Form;
use Illuminate\Console\Command;

class UpdateFormSubmissionMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update-form-submission-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update form submission message translation field';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $forms = Form::all();
        $this->info('Updating ' . $forms->count() . ' forms');
        $forms->each(function ($form) {
            $this->info('Updating form submission message for form: ' . $form->title);
            $form->submission_message_id = I18nHelper::generateI18nItem($form, 'submission_message');
            $form->save();
        });
    }
}
