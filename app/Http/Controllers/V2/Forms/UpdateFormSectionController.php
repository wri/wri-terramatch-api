<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\UpdateFormSectionRequest;
use App\Http\Resources\V2\Forms\FormSectionResource;
use App\Models\V2\Forms\FormSection;
use Illuminate\Support\Facades\Auth;

class UpdateFormSectionController extends Controller
{
    public function __invoke(FormSection $formSection, UpdateFormSectionRequest $updateFormSectionRequest): FormSectionResource
    {
        $formSection->update($updateFormSectionRequest->validated());
        $formSection->save();
        $formSection->form->update([
            'updated_by' => Auth::user()->uuid,
        ]);

        return new FormSectionResource($formSection);
    }
}
