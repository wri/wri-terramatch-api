<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Http\JsonResponse;

class DeleteFormSubmissionController extends Controller
{
    public function __invoke(FormSubmission $formSubmission): JsonResponse
    {
        $this->authorize('delete', $formSubmission);
        if ($formSubmission->status !== FormSubmission::STATUS_STARTED) {
            return new JsonResponse('You can only delete form submissions that have not already been submitted', 406);
        }

        $formSubmission->delete();

        return new JsonResponse('Form submission succesfully deleted', 200);
    }
}
