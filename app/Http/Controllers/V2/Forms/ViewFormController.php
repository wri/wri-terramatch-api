<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormResource;
use App\Models\V2\Forms\Form;
use Illuminate\Http\Request;

class ViewFormController extends Controller
{
    public function __invoke(Request $request, Form $form): FormResource
    {
        return new FormResource($form);
    }
}
