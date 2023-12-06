<?php

namespace App\Http\Controllers\V2\Forms;

use App\Exports\V2\FormSubmissionsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\ExportFormSubmissionRequest;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportFormSubmissionController extends Controller
{
    public function __invoke(ExportFormSubmissionRequest $exportFormSubmissionRequest, Form $form): BinaryFileResponse
    {
        $this->authorize('export', FormSubmission::class);

        $filename = data_get($form, 'title', 'Form') . ' Submission Export - ' . now() . '.csv';

        return (new FormSubmissionsExport(FormSubmission::where('form_id', $form->uuid), $form))->download($filename, Excel::CSV);//->deleteFileAfterSend(true);
    }
}
