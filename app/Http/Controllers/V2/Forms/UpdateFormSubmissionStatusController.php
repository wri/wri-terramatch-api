<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\UpdateFormSubmissionStatusRequest;
use App\Http\Resources\V2\Forms\FormSubmissionResource;
use App\Mail\FormSubmissionApproved;
use App\Mail\FormSubmissionFeedbackReceived;
use App\Mail\FormSubmissionFinalStageApproved;
use App\Mail\FormSubmissionRejected;
use App\Models\Framework;
use App\Models\Notification;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class UpdateFormSubmissionStatusController extends Controller
{
    public function __invoke(FormSubmission $formSubmission, UpdateFormSubmissionStatusRequest $updateFormSubmissionStatusRequest): FormSubmissionResource
    {
        $user = Auth::user();

        $formSubmission->update([
            'status' => $updateFormSubmissionStatusRequest->status,
            'feedback' => $updateFormSubmissionStatusRequest->feedback,
            'feedback_fields' => $updateFormSubmissionStatusRequest->feedback_fields,
        ]);

        switch ($updateFormSubmissionStatusRequest->status) {
            case FormSubmission::STATUS_REQUIRES_MORE_INFORMATION:
                Mail::to($formSubmission->user->email_address)->queue(
                    new FormSubmissionFeedbackReceived(data_get($updateFormSubmissionStatusRequest, 'feedback', null), $user)
                );
                $notification = new Notification([
                    'user_id' => $formSubmission->user->id,
                    'title' => 'Application Updated',
                    'body' => 'You have received feedback on your application',
                    'action' => 'form_submission_update',
                    'referenced_model' => FormSubmission::class,
                    'referenced_model_id' => $formSubmission->id,
                ]);
                $notification->saveOrFail();

                break;
            case FormSubmission::STATUS_REJECTED:
                Mail::to($formSubmission->user->email_address)->queue(
                    new FormSubmissionRejected(data_get($updateFormSubmissionStatusRequest, 'feedback', null), $user)
                );
                $notification = new Notification([
                    'user_id' => $formSubmission->user->id,
                    'title' => 'Application Rejected',
                    'body' => 'Your application has been rejected',
                    'action' => 'form_submission_update',
                    'referenced_model' => FormSubmission::class,
                    'referenced_model_id' => $formSubmission->id,
                ]);
                $notification->saveOrFail();

                break;
            case FormSubmission::STATUS_APPROVED:
                if (empty($formSubmission->stage->nextStage)) {
                    $framework = Framework::where('name', 'Terrafund')->first();
                    if ($framework) {
                        Mail::to($formSubmission->user->email_address)->queue(
                            new FormSubmissionFinalStageApproved(data_get($updateFormSubmissionStatusRequest, 'feedback', null), $user)
                        );

                        $formSubmission->user->frameworks()->syncWithoutDetaching([$framework->id]);
                    }
                } else {
                    Mail::to($formSubmission->user->email_address)->queue(
                        new FormSubmissionApproved(data_get($updateFormSubmissionStatusRequest, 'feedback', null), $user)
                    );
                }

                $notification = new Notification([
                    'user_id' => $formSubmission->user->id,
                    'title' => 'Application Updated',
                    'body' => 'Your application has been approved',
                    'action' => 'form_submission_update',
                    'referenced_model' => FormSubmission::class,
                    'referenced_model_id' => $formSubmission->id,
                ]);
                $notification->saveOrFail();

                break;
        }

        return new FormSubmissionResource($formSubmission);
    }
}
