<?php

namespace App\Http\Controllers\V2\UpdateRequests;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\UpdateRequests\UpdateRequestResource;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\Request;

class AdminViewUpdateRequestController extends Controller
{
    public function __invoke(Request $request,  UpdateRequest $updateRequest): UpdateRequestResource
    {
        $this->authorize('read', $updateRequest);

        return new UpdateRequestResource($updateRequest);
    }
}
