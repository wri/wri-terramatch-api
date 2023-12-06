<?php

namespace App\Http\Controllers\V2\FundingType;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreFundingTypeRequest;
use App\Http\Resources\V2\FundingTypeResource;
use App\Models\V2\FundingType;
use App\Models\V2\Organisation;

class StoreFundingTypeController extends Controller
{
    public function __invoke(StoreFundingTypeRequest $storeFundingTypeRequest): FundingTypeResource
    {
        $model = Organisation::isUuid($storeFundingTypeRequest->organisation_id)->firstOrFail();
        $this->authorize('read', $model);

        $fundingType = FundingType::create($storeFundingTypeRequest->all());

        return new FundingTypeResource($fundingType);
    }
}
