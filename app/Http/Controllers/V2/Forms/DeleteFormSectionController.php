<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormSectionResource;
use App\Models\V2\Forms\FormSection;

class DeleteFormSectionController extends Controller
{
    public function __invoke(FormSection $formSection): FormSectionResource
    {
        $formSection->delete();

        return new FormSectionResource($formSection);
    }
}
