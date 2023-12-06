<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormResource;
use App\Models\V2\Forms\Form;

class PublishFormController extends Controller
{
    public function __invoke(Form $form): FormResource
    {
        if ($form->published) {
            return new FormResource($form);
        }

        Form::query()
            ->where('published', true)
            ->where('stage_id', $form->stage_id)
            ->update([
                'published' => false,
            ]);

        $form->published = true;
        $form->saveOrFail();

        return new FormResource($form);
    }
}
