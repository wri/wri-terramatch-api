<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ReportsControllerTest extends TestCase
{
    protected function callReportingAction($endpoint, $filename)
    {
        $token  = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->get($endpoint, $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain');
        /**
         * This section asserts that the filename is correct (by looking at the
         * content disposition header). However this test occasionally fails
         * when generating the report takes more than one second, so we check
         * three headers (the current second and one second either side of it).
         */
        $contentDispositionHeaders = $response->headers->get("Content-Disposition");
        $now = Carbon::now();
        $possibleContentDispositionHeaders = [
            "Content-Disposition", "attachment; filename=\"" . $filename . "_" . $now->subSecond()->format('Y-m-d_H:i:s') . ".csv\"",
            "Content-Disposition", "attachment; filename=\"" . $filename . "_" . $now->format('Y-m-d_H:i:s') . ".csv\"",
            "Content-Disposition", "attachment; filename=\"" . $filename . "_" . $now->addSecond()->format('Y-m-d_H:i:s') . ".csv\"",
        ];
        $this->assertIsOneOf($possibleContentDispositionHeaders, $contentDispositionHeaders);
    }

    public function testReadAllApprovedOrganisationsAction()
    {
        $this->callReportingAction('/api/reports/approved_organisations', 'approved_organisations');
    }

    public function testReadAllRejectedOrganisationsAction()
    {
        $this->callReportingAction('/api/reports/rejected_organisations', 'rejected_organisations');
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

    public function testReadAllMatchesAction()
    {
        $this->callReportingAction('/api/reports/matches', 'matches');
    }

    public function testReadAllFilterRecordsAction()
    {
        $this->callReportingAction('/api/reports/filter_records', 'filter_records');
    }

    public function testReadAllOrganisationsAction()
    {
        $this->callReportingAction('/api/reports/organisations', 'organisations');
    }

    public function testReadAllPitchesAction()
    {
        $this->callReportingAction('/api/reports/pitches', 'pitches');
    }

    public function testReadAllMonitoringsAction()
    {
        $this->callReportingAction('/api/reports/monitorings', 'monitorings');
    }

    public function testReadAllProgressUpdatesAction()
    {
        $this->callReportingAction('/api/reports/progress_updates', 'progress_updates');
    }
}
