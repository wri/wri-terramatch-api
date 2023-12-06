<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\StoreFormSectionRequest;
use App\Http\Resources\V2\Forms\FormSectionResource;
use App\Models\V2\Forms\FormSection;
use Illuminate\Support\Facades\Auth;

class StoreFormSectionController extends Controller
{
    public function __invoke(StoreFormSectionRequest $storeFormSectionRequest): FormSectionResource
    {
        $data = $storeFormSectionRequest->validated();
        $formSection = FormSection::create($data);
        $formSection->form->update([
            'updated_by' => Auth::user()->uuid,
        ]);

        return new FormSectionResource($formSection);
    }
}
