<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormResource;
use App\Models\V2\Forms\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ViewFormController extends Controller
{
    public function __invoke(Request $request, Form $form): FormResource
    {
        if ($request->query('lang')) {
            App::setLocale($request->query('lang'));
        }

        return new FormResource($form);
    }
}
