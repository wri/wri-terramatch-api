<?php

namespace App\Http\Controllers\V2\Leaderships;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateLeadershipsRequest;
use App\Http\Resources\V2\LeadershipsResource;
use App\Models\V2\Leaderships;

class UpdateLeadershipsController extends Controller
{
    public function __invoke(Leaderships $leaderships, UpdateLeadershipsRequest $updateLeadershipsRequest): LeadershipsResource
    {
        $this->authorize('read', $leaderships->organisation);

        $leaderships->update($updateLeadershipsRequest->validated());
        $leaderships->save();

        return new LeadershipsResource($leaderships);
    }
}
