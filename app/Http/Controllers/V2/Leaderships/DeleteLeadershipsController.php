<?php

namespace App\Http\Controllers\V2\Leaderships;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\LeadershipsResource;
use App\Models\V2\Leaderships;

class DeleteLeadershipsController extends Controller
{
    public function __invoke(Leaderships $leaderships): LeadershipsResource
    {
        $this->authorize('update', $leaderships->organisation);
        $leaderships->delete();
        $leaderships->save();

        return new LeadershipsResource($leaderships);
    }
}
