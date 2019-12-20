<?php

namespace App\Http\Controllers;

use App\Exports\ApprovedOrganisationsExport;
use App\Exports\FiltersExport;
use App\Exports\OffersExport;
use App\Exports\OrganisationsExport;
use App\Exports\FundingAmountExport;
use App\Exports\OrganisationVersionsExport;
use App\Exports\PitchBenefitedPeopleExport;
use App\Exports\UsersExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\ApprovedPitchesExport;
use App\Exports\InterestsExport;
use App\Exports\MatchesExport;
use App\Exports\RestoredHectaresExport;
use App\Exports\RejectedPitchesExport;

class ReportsController extends Controller
{
    public function readAllApprovedOrganisationsAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new ApprovedOrganisationsExport())->download('approved_organisations_'. $date .'.csv');
    }

    public function readAllOrganisationsAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new OrganisationsExport())->download('organisations_'. $date .'.csv');
    }

    public function readAllOrganisationVersionsAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new OrganisationVersionsExport())->download('organisation_versions_'. $date .'.csv');
    }

    public function readAllUsersAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new UsersExport())->download('users_'. $date .'.csv');
    }

    public function readAllOffersAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new OffersExport())->download('offers_'. $date .'.csv');
    }

    public function readAllApprovedPitchesAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new ApprovedPitchesExport())->download('approved_pitches_'. $date .'.csv');
    }

    public function readAllRejectedPitchesAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new RejectedPitchesExport())->download('rejected_pitches_'. $date .'.csv');
    }

    public function readAllInterestsAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new InterestsExport())->download('interests_'. $date .'.csv');
    }

    public function readAllFundingAmountAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new FundingAmountExport())->download('funding_amount_'. $date .'.csv');
    }

    public function readAllMatchesAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new MatchesExport())->download('matches_'. $date .'.csv');
    }

    public function readAllBenefitedPeopleAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new PitchBenefitedPeopleExport())->download('pitches_benefited_people_'. $date .'.csv');
    }

    public function readRestoredHectaresAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new RestoredHectaresExport())->download('restored_hectares_'. $date .'.csv');
    }

    public function readAllFiltersRecordsAction(Request $request): BinaryFileResponse
    {
        $this->authorize('isAdmin');
        $date = Carbon::now()->format('Y-m-d');
        return (new FiltersExport())->download('filter_records_'. $date .'.csv');
    }
}
