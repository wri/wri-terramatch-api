<?php

namespace App\Http\Controllers\V2\UpdateRequests;

use App\Http\Controllers\Controller;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSoftDeleteUpdateRequestController extends Controller
{
    public function __invoke(Request $request, UpdateRequest $updateRequest): JsonResponse
    {
        $this->authorize('delete', $updateRequest);

        $entity = $updateRequest->updaterequestable;
        $entity->update_request_status = UpdateRequest::ENTITY_STATUS_NO_UPDATE;
        $entity->save();

        $updateRequest->delete();

        return new JsonResponse('Update request successfully deleted', 200);
    }
}
