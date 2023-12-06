<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormSubmissionResource;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormSubmissionNextStageController extends Controller
{
    public function __invoke(FormSubmission $formSubmission, Request $request): FormSubmissionResource
    {
        $this->authorize('update', $formSubmission);

        $nextStage = $formSubmission->stage->nextStage;

        if (! empty($nextStage)) {
            $existingSubmission = FormSubmission::where('application_id', $formSubmission->application_id)
                ->where('stage_uuid', $nextStage->uuid)
                ->first();

            if (! empty($existingSubmission->id)) {
                return new FormSubmissionResource($existingSubmission);
            }

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

            $formSubmission->application->update(['updated_by' => Auth::user()->id]);

            return new FormSubmissionResource($nextForm);
        }

        return new FormSubmissionResource($formSubmission);
    }

    private function existingFormSubmission()
    {
    }
}
