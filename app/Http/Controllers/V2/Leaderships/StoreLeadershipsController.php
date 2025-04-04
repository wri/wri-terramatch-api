<?php

namespace App\Http\Controllers\V2\Leaderships;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreLeadershipsRequest;
use App\Http\Resources\V2\LeadershipsResource;
use App\Models\V2\Leaderships;
use App\Models\V2\Organisation;
use \Illuminate\Support\Facades\Log;


class StoreLeadershipsController extends Controller
{
    public function __invoke(StoreLeadershipsRequest $storeLeadershipsRequest): LeadershipsResource
    {
        $model = Organisation::isUuid($storeLeadershipsRequest->organisation_id)->firstOrFail();
        $this->authorize('read', $model);
        Log::info($model);
        $storeLeadershipsRequest['organisation_id'] = $model->id;
        $leaderships = Leaderships::create($storeLeadershipsRequest->all());

        return new LeadershipsResource($leaderships);
    }
}
