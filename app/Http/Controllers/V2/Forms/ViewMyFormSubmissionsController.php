<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormSubmissionsCollection;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class ViewMyFormSubmissionsController extends Controller
{
    public function __invoke(Request $request): FormSubmissionsCollection
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $collection = FormSubmission::query()
            ->where('user_id', Auth::user()->uuid)
            ->paginate($perPage);

        if ($request->query('lang')) {
            App::setLocale($request->query('lang'));
        }

        return new FormSubmissionsCollection($collection);
    }
}
