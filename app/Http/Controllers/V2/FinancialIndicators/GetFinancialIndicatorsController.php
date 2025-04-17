<?php

namespace App\Http\Controllers\V2\FinancialIndicators;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\FinancialIndicatorsResource;
use App\Models\V2\FinancialIndicators;
use Illuminate\Http\Request;
use App\Models\V2\Organisation;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GetFinancialIndicatorsController extends Controller
{
    public function __invoke(Request $request, string $organisationId): ResourceCollection
    {
        $organisationId = Organisation::isUuid($organisationId)->value('id');
        $financialIndicators = FinancialIndicators::where("organisation_id", $organisationId)
            ->where('collection', $request->collection)->get();

        return FinancialIndicatorsResource::collection($financialIndicators);
    }
}
