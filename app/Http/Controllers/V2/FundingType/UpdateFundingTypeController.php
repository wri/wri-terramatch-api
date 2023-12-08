<?php

namespace App\Http\Controllers\V2\FundingType;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateFundingTypeRequest;
use App\Http\Resources\V2\FundingTypeResource;
use App\Models\V2\FundingType;

class UpdateFundingTypeController extends Controller
{
    public function __invoke(FundingType $fundingType, UpdateFundingTypeRequest $updateFundingTypeRequest): FundingTypeResource
    {
        $this->authorize('read', $fundingType->organisation);
        $fundingType->update($updateFundingTypeRequest->validated());
        $fundingType->save();

        return new FundingTypeResource($fundingType);
    }
}
