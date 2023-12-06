<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormQuestionResource;
use App\Models\V2\Forms\FormQuestion;

class DeleteFormQuestionController extends Controller
{
    public function __invoke(FormQuestion $formQuestion): FormQuestionResource
    {
        $formQuestion->delete();

        return new FormQuestionResource($formQuestion);
    }
}
