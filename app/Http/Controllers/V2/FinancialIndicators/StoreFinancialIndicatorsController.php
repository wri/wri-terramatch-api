<?php

namespace App\Http\Controllers\V2\FinancialIndicators;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreFinancialIndicatorsRequest;
use App\Http\Resources\V2\FinancialIndicatorsResource;
use App\Models\V2\FinancialIndicators;
use App\Models\V2\Organisation;

class StoreFinancialIndicatorsController extends Controller
{
    public function __invoke(StoreFinancialIndicatorsRequest $storeFinancialIndicatorsRequest): FinancialIndicatorsResource
    {
        $model = Organisation::isUuid($storeFinancialIndicatorsRequest->organisation_id)->firstOrFail();
        $this->authorize('read', $model);

        $storeFinancialIndicatorsRequest['organisation_id'] = $model->id;
        $FinancialIndicators = FinancialIndicators::create($storeFinancialIndicatorsRequest->all());

        return new FinancialIndicatorsResource($FinancialIndicators);
    }
}
