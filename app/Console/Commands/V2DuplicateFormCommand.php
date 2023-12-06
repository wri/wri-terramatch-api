<?php

namespace App\Console\Commands;

use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\Forms\FormTableHeader;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class V2DuplicateFormCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-duplicate-form {oldUuid} {frameworkKey}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Duplicate a form and everything attached to it';

    public function handle()
    {
        $form = Form::isUuid($this->argument('oldUuid'))->firstOrFail();

        $newForm = $form->replicate();
        $newForm->framework_key = $this->argument('frameworkKey');
        $newForm->uuid = Str::uuid()->toString();
        $newForm->save();

        $form->sections->each(function (FormSection $formSection) use ($newForm) {
            $newFormSection = $formSection->replicate();
            $newFormSection->uuid = Str::uuid()->toString();
            $newFormSection->form_id = $newForm->uuid;
            $newFormSection->save();

            $newFormSection->questions->each(function (FormQuestion $question) use ($newFormSection) {
                $newQuestion = $question->replicate();
                $newQuestion->uuid = Str::uuid()->toString();
                $newQuestion->form_section_id = $newFormSection->id;
                $newQuestion->save();

                $newQuestion->options->each(function (FormQuestionOption $questionOption) use ($newQuestion) {
                    $newOption = $questionOption->replicate();
                    $newOption->uuid = Str::uuid()->toString();
                    $newOption->form_question_id = $newQuestion->id;
                    $newOption->save();
                });

                $newQuestion->tableHeaders->each(function (FormTableHeader $formTableHeader) use ($newQuestion) {
                    $newTableHeader = $formTableHeader->replicate();
                    $newTableHeader->uuid = Str::uuid()->toString();
                    $newTableHeader->form_question_id = $newQuestion->id;
                    $newTableHeader->save();
                });
            });
        });
    }
}
