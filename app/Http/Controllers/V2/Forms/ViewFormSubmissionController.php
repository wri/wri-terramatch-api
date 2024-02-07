<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormSubmissionResource;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Http\Request;

class ViewFormSubmissionController extends Controller
{
    public function __invoke(Request $request, FormSubmission $formSubmission): FormSubmissionResource
    {
        $this->authorize('read', $formSubmission);

        return new FormSubmissionResource($formSubmission);
    }
}
