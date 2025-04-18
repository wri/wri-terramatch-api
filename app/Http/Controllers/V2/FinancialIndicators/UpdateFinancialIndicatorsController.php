<?php

namespace App\Http\Controllers\V2\FinancialIndicators;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateFinancialIndicatorsRequest;
use App\Http\Resources\V2\FinancialIndicatorsResource;
use App\Models\V2\FinancialIndicators;

class UpdateFinancialIndicatorsController extends Controller
{
    public function __invoke(FinancialIndicators $financialIndicators, UpdateFinancialIndicatorsRequest $updateFinancialIndicatorsRequest): FinancialIndicatorsResource
    {
        $this->authorize('read', $financialIndicators->organisation);
        $financialIndicators->update($updateFinancialIndicatorsRequest->validated());
        $financialIndicators->save();

        return new FinancialIndicatorsResource($financialIndicators);
    }
}
