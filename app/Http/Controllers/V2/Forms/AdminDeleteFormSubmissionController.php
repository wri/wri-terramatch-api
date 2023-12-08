<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormSubmissionResource;
use App\Models\V2\Forms\FormSubmission;

class AdminDeleteFormSubmissionController extends Controller
{
    public function __invoke(FormSubmission $formSubmission): FormSubmissionResource
    {
        $formSubmission->delete();

        return new FormSubmissionResource($formSubmission);
    }
}
