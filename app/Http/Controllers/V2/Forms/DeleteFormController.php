<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormResource;
use App\Models\V2\Forms\Form;

class DeleteFormController extends Controller
{
    public function __invoke(Form $form)
    {
        if ($form->published) {
            return response()->json(['error' => 'You cannot delete a published form'], 422);
        }

        $form->delete();

        return new FormResource($form);
    }
}
