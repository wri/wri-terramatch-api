<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\StoreFormSubmissionRequest;
use App\Http\Resources\V2\Forms\FormSubmissionResource;
use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\ProjectPitch;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class StoreFormSubmissionController extends Controller
{
    public function __invoke(StoreFormSubmissionRequest $storeFormSubmissionRequest): FormSubmissionResource
    {
        $data = $storeFormSubmissionRequest->validated();

        $form = Form::isUuid(data_get($data, 'form_uuid'))->first();
        if (data_get($data, 'project_pitch_uuid', false)) {
            $projectPitch = ProjectPitch::isUuid(data_get($data, 'project_pitch_uuid'))->first();
            $projectPitch->update([
                'funding_programme_id' => data_get($form->stage, 'funding_programme_id'),
            ]);
        } else {
            $projectPitch = ProjectPitch::create([
                'organisation_id' => Auth::user()->organisation->uuid,
                'funding_programme_id' => data_get($form->stage, 'funding_programme_id'),
            ]);
        }
        $organisation = $projectPitch->organisation;
        if (data_get($data, 'application_uuid', false)) {
            $application = Application::isUuid(data_get($data, 'application_uuid'))->first();
        } else {
            $application = Application::create([
                'organisation_uuid' => $organisation->uuid,
                'funding_programme_uuid' => data_get($form->stage, 'funding_programme_id'),
                'updated_by' => Auth::user()->id,
            ]);
        }

        $formSubmission = FormSubmission::create([
            'form_id' => $form->uuid,
            'stage_uuid' => $form->stage_id,
            'user_id' => Auth::user()->uuid,
            'organisation_uuid' => $organisation->uuid,
            'project_pitch_uuid' => $projectPitch->uuid,
            'application_id' => $application->id,
            'status' => FormSubmission::STATUS_STARTED,
            'answers' => [],
        ]);

        if ($storeFormSubmissionRequest->query('lang')) {
            App::setLocale($storeFormSubmissionRequest->query('lang'));
        }

        return new FormSubmissionResource($formSubmission);
    }
}
