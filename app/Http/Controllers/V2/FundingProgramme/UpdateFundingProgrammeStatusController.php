<?php

namespace App\Http\Controllers\V2\FundingProgramme;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateFundingProgrammeStatusRequest;
use App\Http\Resources\V2\FundingProgrammes\FundingProgrammeResource;
use App\Models\V2\FundingProgramme;

class UpdateFundingProgrammeStatusController extends Controller
{
    public function __invoke(FundingProgramme $fundingProgramme, UpdateFundingProgrammeStatusRequest $updateFundingProgrammeStatusRequest): FundingProgrammeResource
    {
        $fundingProgramme->update($updateFundingProgrammeStatusRequest->validated());
        $fundingProgramme->save();

        return new FundingProgrammeResource($fundingProgramme);
    }
}
