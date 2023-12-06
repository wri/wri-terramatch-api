<?php

namespace App\Http\Controllers\V2\Stratas;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Stratas\StrataResource;
use App\Models\V2\Stratas\Strata;

class DeleteStrataController extends Controller
{
    public function __invoke(Strata $strata): StrataResource
    {
        $this->authorize('update', $strata->stratasable);
        $strata->delete();
        $strata->save();

        return new StrataResource($strata);
    }
}
