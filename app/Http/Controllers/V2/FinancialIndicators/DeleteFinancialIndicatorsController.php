<?php

namespace App\Http\Controllers\V2\FinancialIndicators;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\FinancialIndicatorsResource;
use App\Models\V2\FinancialIndicators;

class DeleteFinancialIndicatorsController extends Controller
{
    public function __invoke(FinancialIndicators $financialIndicators): FinancialIndicatorsResource
    {
        $this->authorize('update', $financialIndicators->organisation);
        $financialIndicators->delete();
        $financialIndicators->save();

        return new FinancialIndicatorsResource($financialIndicators);
    }
}
