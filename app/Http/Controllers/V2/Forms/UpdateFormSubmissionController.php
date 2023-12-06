<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\UpdateFormSubmissionRequest;
use App\Http\Resources\V2\Forms\AnswersCollection;
use App\Http\Resources\V2\Forms\FormSubmissionResource;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class UpdateFormSubmissionController extends Controller
{
    public function __invoke(FormSubmission $formSubmission, UpdateFormSubmissionRequest $updateFormSubmissionRequest): FormSubmissionResource
    {
        $this->authorize('update', $formSubmission);

        $data = $updateFormSubmissionRequest->validated();

        $data['answers'] = $formSubmission->updateAllAnswers($updateFormSubmissionRequest->get('answers')); //AnswersCollection::fromArray($updateFormSubmissionRequest->get('answers'));
        $data['user_id'] = Auth::user()->uuid;

        $formSubmission->update($data);
        if ($formSubmission->application) {
            $formSubmission->application->update(['updated_by' => Auth::user()->id]);
        }

        if ($updateFormSubmissionRequest->query('lang')) {
            App::setLocale($updateFormSubmissionRequest->query('lang'));
        }

        return new FormSubmissionResource($formSubmission);
    }
}
