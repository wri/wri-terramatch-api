<?php

namespace App\Http\Controllers\V2\Forms;

use App\Events\V2\Application\ApplicationSubmittedEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormSubmissionResource;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmitFormSubmissionController extends Controller
{
    public function __invoke(FormSubmission $formSubmission, Request $request): FormSubmissionResource
    {
        $this->authorize('update', $formSubmission);

        $formSubmission->status = FormSubmission::STATUS_AWAITING_APPROVAL;
        $formSubmission->feedback = null;
        $formSubmission->feedback_fields = null;
        $formSubmission->save();

        ApplicationSubmittedEvent::dispatch($request->user(), $formSubmission);

        if ($formSubmission->application) {
            $formSubmission->application->update(['updated_by' => Auth::user()->id]);
            $formSubmission->application->touch();
        }

        return new FormSubmissionResource($formSubmission);
    }
}
