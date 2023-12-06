<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormSubmissionResource;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ViewFormSubmissionController extends Controller
{
    public function __invoke(Request $request, FormSubmission $formSubmission): FormSubmissionResource
    {
        $this->authorize('read', $formSubmission);

        if ($request->query('lang')) {
            App::setLocale($request->query('lang'));
        }

        return new FormSubmissionResource($formSubmission);
    }
}
