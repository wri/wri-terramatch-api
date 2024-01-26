<?php

namespace App\Http\Controllers\V2\Nurseries;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Nurseries\NurseyWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ViewNurseryWithFormController extends Controller
{
    public function __invoke(Request $request, Nursery $nursery): NurseyWithSchemaResource
    {
        $this->authorize('read', $nursery);

        if ($request->query('lang')) {
            App::setLocale($request->query('lang'));
        }

        $schema = Form::where('framework_key', $nursery->framework_key)
            ->where('model', Nursery::class)
            ->first();

        return new NurseyWithSchemaResource($nursery, ['schema' => $schema]);
    }
}
