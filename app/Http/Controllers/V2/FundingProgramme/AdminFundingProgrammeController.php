<?php

namespace App\Http\Controllers\V2\FundingProgramme;

use App\Helpers\I18nHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateFundingProgrammeRequest;
use App\Http\Resources\V2\FundingProgrammes\FundingProgrammeResource;
use App\Models\V2\FundingProgramme;

class AdminFundingProgrammeController extends Controller
{
    public function update(UpdateFundingProgrammeRequest $updateFundingProgrammeRequest, FundingProgramme $fundingProgramme): FundingProgrammeResource
    {
        $fundingProgramme->update($updateFundingProgrammeRequest->validated());
        $fundingProgramme->name_id = I18nHelper::generateI18nItem($fundingProgramme, 'name');
        $fundingProgramme->description_id = I18nHelper::generateI18nItem($fundingProgramme, 'description');
        $fundingProgramme->location_id = I18nHelper::generateI18nItem($fundingProgramme, 'location');
        $fundingProgramme->save();

        return new FundingProgrammeResource($fundingProgramme);
    }
}
