<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormOptionListOptionResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CommonOptionsIndexController extends Controller
{
    public function __invoke(Request $request, String $key): ResourceCollection
    {
        $this->authorize('listLinkedFields', Form::class);

        if ($request->query('search')) {
            $formOptionList = FormOptionList::where('key', $key)->first();
            if ($formOptionList) {
                $qry = FormOptionListOption::search(trim($request->query('search')))
                    ->where('form_option_list_id', $formOptionList->id);
            }
        } else {
            $formOptionList = FormOptionList::where('key', $key)->first();
            if ($formOptionList) {
                $qry = FormOptionListOption::where('form_option_list_id', $formOptionList->id);
            }
        }

        return FormOptionListOptionResource::collection($qry->get());
    }
}
