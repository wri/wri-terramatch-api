<?php

namespace App\Http\Controllers\V2\Stratas;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Stratas\UpdateStrataRequest;
use App\Http\Resources\V2\Stratas\StrataResource;
use App\Models\V2\Stratas\Strata;

class UpdateStrataController extends Controller
{
    public function __invoke(Strata $strata, UpdateStrataRequest $updateStrataRequest): StrataResource
    {
        $this->authorize('update', $strata->stratasable);
        $strata->update($updateStrataRequest->validated());
        $strata->save();

        return new StrataResource($strata);
    }
}
