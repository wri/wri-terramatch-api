<?php

namespace App\Http\Controllers\V2\FundingProgramme;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreFundingProgrammeRequest;
use App\Http\Requests\V2\UpdateFundingProgrammeRequest;
use App\Http\Resources\V2\FundingProgrammes\FundingProgrammeCollection;
use App\Http\Resources\V2\FundingProgrammes\FundingProgrammeResource;
use App\Models\V2\FundingProgramme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class FundingProgrammeController extends Controller
{
    public function index(Request $request): FundingProgrammeCollection
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $myOrganisationTypes = Auth::user()->all_my_organisations->pluck('type')->toArray();

        $collection = FundingProgramme::query()
            ->where(function ($query) use ($myOrganisationTypes) {
                foreach ($myOrganisationTypes as $organisationType) {
                    $query->orWhereJsonContains('organisation_types', $organisationType);
                }
            })
            ->with('stages')->paginate($perPage);

        if ($request->query('lang')) {
            App::setLocale($request->query('lang'));
        }

        return new FundingProgrammeCollection($collection);
    }

    public function store(StoreFundingProgrammeRequest $storeFundingProgrammeRequest): FundingProgrammeResource
    {
        $fundingProgramme = FundingProgramme::create($storeFundingProgrammeRequest->validated());

        return new FundingProgrammeResource($fundingProgramme);
    }

    public function show(FundingProgramme $fundingProgramme): FundingProgrammeResource
    {
        return new FundingProgrammeResource($fundingProgramme);
    }

    public function update(UpdateFundingProgrammeRequest $updateFundingProgrammeRequest, FundingProgramme $fundingProgramme): FundingProgrammeResource
    {
        $fundingProgramme->update($updateFundingProgrammeRequest->validated());
        $fundingProgramme->save();

        return new FundingProgrammeResource($fundingProgramme);
    }

    public function destroy(FundingProgramme $fundingProgramme): JsonResponse
    {
        $fundingProgramme->delete();

        return response()->json([], 202);
    }
}
