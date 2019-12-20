<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class ReportsControllerTest extends TestCase
{
    protected function callReportingAction($endpoint, $filename)
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->get($endpoint, $headers);
        $filename .= "_" . Carbon::now()->format('Y-m-d') . ".csv";
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain');
        $response->assertHeader('Content-Disposition', 'attachment; filename=' . $filename);
    }

    public function testReadAllApprovedOrganisationsAction()
    {
        $this->callReportingAction('/api/reports/approved_organisations', 'approved_organisations');
    }

    public function testReadAllOrganisationsAction()
    {
        $this->callReportingAction('/api/reports/organisations', 'organisations');
    }

    public function testReadAllUsersAction()
    {
        $this->callReportingAction('/api/reports/users', 'users');
    }

    public function testReadAllOffersAction()
    {
        $this->callReportingAction('/api/reports/offers', 'offers');
    }

    public function testReadAllApprovedPitchesAction()
    {
        $this->callReportingAction('/api/reports/approved_pitches', 'approved_pitches');
    }

    public function testReadAllRejectedPitchesAction()
    {
        $this->callReportingAction('/api/reports/rejected_pitches', 'rejected_pitches');
    }

    public function testReadAllInterestsAction()
    {
        $this->callReportingAction('/api/reports/interests', 'interests');
    }

    public function testReadAllFundingAmountAction()
    {
        $this->callReportingAction('/api/reports/funding_amount', 'funding_amount');
    }

    public function testReadAllMatchesAction()
    {
        $this->callReportingAction('/api/reports/matches', 'matches');
    }

    public function testReadRestoredHectaresAction()
    {
        $this->callReportingAction('/api/reports/restored_hectares', 'restored_hectares');
    }

    public function testReadAllBenefitedPeopleAction()
    {
        $this->callReportingAction('/api/reports/pitches_benefited_people', 'pitches_benefited_people');
    }

    public function testReadAllOrganisationVersionsAction()
    {
        $this->callReportingAction('/api/reports/organisation_versions', 'organisation_versions');
    }

    public function testReadAllFiltersRecordsAction()
    {
        $this->callReportingAction('/api/reports/filter_records', 'filter_records');
    }
}
