<?php

namespace App\Http\Controllers\V2\FundingProgramme;

use App\Helpers\I18nHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreFundingProgrammeRequest;
use App\Http\Requests\V2\UpdateFundingProgrammeRequest;
use App\Http\Resources\V2\FundingProgrammes\FundingProgrammeCollection;
use App\Http\Resources\V2\FundingProgrammes\FundingProgrammeResource;
use App\Models\V2\FundingProgramme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Spatie\QueryBuilder\QueryBuilder;

class AdminFundingProgrammeController extends Controller
{
    public function index(Request $request): FundingProgrammeCollection
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $query = FundingProgramme::query()->with('stages');

        QueryBuilder::for($query)
            ->allowedFilters([
                'uuid',
            ]);

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        if ($request->query('lang')) {
            App::setLocale($request->query('lang'));
        }

        return new FundingProgrammeCollection($collection);
    }

    public function store(StoreFundingProgrammeRequest $storeFundingProgrammeRequest): FundingProgrammeResource
    {
        $fundingProgramme = FundingProgramme::create($storeFundingProgrammeRequest->validated());
        $fundingProgramme->name_id = I18nHelper::generateI18nItem($fundingProgramme, 'name');
        $fundingProgramme->description_id = I18nHelper::generateI18nItem($fundingProgramme, 'description');
        $fundingProgramme->save();

        return new FundingProgrammeResource($fundingProgramme);
    }

    public function show(FundingProgramme $fundingProgramme): FundingProgrammeResource
    {
        return new FundingProgrammeResource($fundingProgramme);
    }

    public function update(UpdateFundingProgrammeRequest $updateFundingProgrammeRequest, FundingProgramme $fundingProgramme): FundingProgrammeResource
    {
        $fundingProgramme->update($updateFundingProgrammeRequest->validated());
        $fundingProgramme->name_id = I18nHelper::generateI18nItem($fundingProgramme, 'name');
        $fundingProgramme->description_id = I18nHelper::generateI18nItem($fundingProgramme, 'description');
        $fundingProgramme->save();

        return new FundingProgrammeResource($fundingProgramme);
    }

    public function destroy(FundingProgramme $fundingProgramme): JsonResponse
    {
        $fundingProgramme->delete();

        return response()->json([], 202);
    }
}
