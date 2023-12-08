<?php

namespace Tests\Legacy\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class ReportsControllerTest extends LegacyTestCase
{
    protected function callReportingAction(string $endpoint, string $filename, string $expectedHeader = 'text/csv; charset=UTF-8')
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->get($endpoint, $headers);
        $response->assertStatus(200);
        /**
         * This section asserts that the filename is correct (by looking at the
         * content disposition header). However this test occasionally fails
         * when generating the report takes more than one second, so we check
         * three headers (the current second and one second either side of it).
         */
        $contentDispositionHeaders = $response->headers->get('Content-Disposition');
        $now = Carbon::now();
        $possibleContentDispositionHeaders = [
            'Content-Disposition', 'attachment; filename="' . $filename . '_' . $now->subSecond()->format('Y-m-d_H:i:s') . '.csv"',
            'Content-Disposition', 'attachment; filename="' . $filename . '_' . $now->format('Y-m-d_H:i:s') . '.csv"',
            'Content-Disposition', 'attachment; filename="' . $filename . '_' . $now->addSecond()->format('Y-m-d_H:i:s') . '.csv"',
        ];
        $this->assertIsOneOf($possibleContentDispositionHeaders, $contentDispositionHeaders);
    }

    public function testReadAllApprovedOrganisationsAction(): void
    {
        $this->callReportingAction('/api/reports/approved_organisations', 'approved_organisations');
    }

    public function testReadAllRejectedOrganisationsAction(): void
    {
        $this->callReportingAction('/api/reports/rejected_organisations', 'rejected_organisations', 'text/plain; charset=UTF-8');
    }

    public function testReadAllUsersAction(): void
    {
        $this->callReportingAction('/api/reports/users', 'users');
    }

    public function testReadAllOffersAction(): void
    {
        $this->callReportingAction('/api/reports/offers', 'offers');
    }

    public function testReadAllApprovedPitchesAction(): void
    {
        $this->callReportingAction('/api/reports/approved_pitches', 'approved_pitches');
    }

    public function testReadAllRejectedPitchesAction(): void
    {
        $this->callReportingAction('/api/reports/rejected_pitches', 'rejected_pitches', 'text/plain; charset=UTF-8');
    }

    public function testReadAllInterestsAction(): void
    {
        $this->callReportingAction('/api/reports/interests', 'interests');
    }

    public function testReadAllMatchesAction(): void
    {
        $this->callReportingAction('/api/reports/matches', 'matches');
    }

    public function testReadAllFilterRecordsAction(): void
    {
        $this->callReportingAction('/api/reports/filter_records', 'filter_records', 'text/plain; charset=UTF-8');
    }

    public function testReadAllOrganisationsAction(): void
    {
        $this->callReportingAction('/api/reports/organisations', 'organisations');
    }

    public function testReadAllPitchesAction(): void
    {
        $this->callReportingAction('/api/reports/pitches', 'pitches');
    }

    public function testReadAllMonitoringsAction(): void
    {
        $this->callReportingAction('/api/reports/monitorings', 'monitorings', 'text/plain; charset=UTF-8');
    }

    public function testReadAllProgressUpdatesAction(): void
    {
        $this->callReportingAction('/api/reports/progress_updates', 'progress_updates');
    }
}
