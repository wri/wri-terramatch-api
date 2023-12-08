<?php

namespace App\Observers\V2;

use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormSection;

class FormObserver
{
    public function deleted(Form $form): void
    {
        $form->stage()->delete();
        $form->sections->each(function (FormSection $formSection) {
            $this->deleteFormSection($formSection);
        });
    }

    private function deleteFormSection(FormSection $formSection)
    {
        $formSection->questions->each(function (FormQuestion $formQuestion) {
            $this->deleteFormQuestion($formQuestion);
        });

        $formSection->delete();
    }

    private function deleteFormQuestion(FormQuestion $formQuestion)
    {
        $formQuestion->children()->delete();
        $formQuestion->options()->delete();
        $formQuestion->tableHeaders()->delete();
        $formQuestion->delete();
    }
}
