<?php

namespace App\Listeners\v2\Form;

use App\Events\V2\Form\FormSubmissionApprovedEvent;
use App\Models\V2\Forms\FormSubmission;

class FormSubmissionNextStage
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\v2\application\ApplicationSubmittedEvent  $event
     * @return void
     */
    public function handle(FormSubmissionApprovedEvent $event)
    {
        /*
        $formSubmission = $event->formSubmission;

        $nextStage = $formSubmission->stage->nextStage;

        if (! empty($nextStage)) {
            $form = $nextStage->form;
            $nextForm = FormSubmission::create([
                'application_id' => $formSubmission->application_id,
                'organisation_uuid' => $formSubmission->organisation_uuid,
                'project_pitch_uuid' => $formSubmission->project_pitch_uuid,
                'user_id' => $formSubmission->user_id,
                'stage_uuid' => $nextStage->uuid,
                'form_id' => $form->uuid,
                'status' => FormSubmission::STATUS_STARTED,
                'answers' => '[]',
            ]);
        }
        */
    }
}
