<?php

namespace App\Http\Controllers\V2\FundingType;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\FundingTypeResource;
use App\Models\V2\FundingType;

class DeleteFundingTypeController extends Controller
{
    public function __invoke(FundingType $fundingType): FundingTypeResource
    {
        $this->authorize('update', $fundingType->organisation);
        $fundingType->delete();
        $fundingType->save();

        return new FundingTypeResource($fundingType);
    }
}
