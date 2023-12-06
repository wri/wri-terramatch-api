<?php

namespace App\Observers\V2;

use App\Models\V2\Forms\FormSubmission;

class FormSubmissionObserver
{
    public function deleted(FormSubmission $formSubmission): void
    {
        $this->deleteApplicationIfLastFormSubmission($formSubmission);
    }

    private function deleteApplicationIfLastFormSubmission(FormSubmission $formSubmission): void
    {
        $application = $formSubmission->application;

        if ($application && $application->formSubmissions->whereNull('deleted_at')->count() == 0) {
            $application->delete();
        }
    }
}
