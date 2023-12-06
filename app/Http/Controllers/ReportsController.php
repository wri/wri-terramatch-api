<?php

namespace App\Http\Controllers;

use App\Exports\ApprovedOrganisationsExport;
use App\Exports\ApprovedPitchesExport;
use App\Exports\FilterRecordsExport;
use App\Exports\InterestsExport;
use App\Exports\MatchesExport;
use App\Exports\MonitoringsExport;
use App\Exports\OffersExport;
use App\Exports\OrganisationsExport;
use App\Exports\PitchesExport;
use App\Exports\ProgressUpdatesExport;
use App\Exports\RejectedOrganisationsExport;
use App\Exports\RejectedPitchesExport;
use App\Exports\UsersExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportsController extends Controller
{
    private function getDateTime(): string
    {
        return Carbon::now()->format('Y-m-d_H:i:s');
    }

    public function readAllApprovedOrganisationsAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('approvedOrganisationsReport', $duration, function () {
            return new ApprovedOrganisationsExport();
        });

        return ($report)->download('approved_organisations_' . $this->getDateTime() . '.csv');
    }

    public function readAllRejectedOrganisationsAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('rejectedOrganisationsReport', $duration, function () {
            return new RejectedOrganisationsExport();
        });

        return ($report)->download('rejected_organisations_' . $this->getDateTime() . '.csv');
    }

    public function readAllApprovedPitchesAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('approvedPitchesReport', $duration, function () {
            return new ApprovedPitchesExport();
        });

        return ($report)->download('approved_pitches_' . $this->getDateTime() . '.csv');
    }

    public function readAllFilterRecordsAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('filterRecordsReport', $duration, function () {
            return new FilterRecordsExport();
        });

        return ($report)->download('filter_records_' . $this->getDateTime() . '.csv');
    }

    public function readAllInterestsAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('interestsReport', $duration, function () {
            return new InterestsExport();
        });

        return ($report)->download('interests_' . $this->getDateTime() . '.csv');
    }

    public function readAllMatchesAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('matchesReport', $duration, function () {
            return new MatchesExport();
        });

        return ($report)->download('matches_' . $this->getDateTime() . '.csv');
    }

    public function readAllOffersAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('offersReport', $duration, function () {
            return new OffersExport();
        });

        return ($report)->download('offers_' . $this->getDateTime() . '.csv');
    }

    public function readAllRejectedPitchesAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('rejectedPitchesReport', $duration, function () {
            return new RejectedPitchesExport();
        });

        return ($report)->download('rejected_pitches_' . $this->getDateTime() . '.csv');
    }

    public function readAllUsersAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('usersReport', $duration, function () {
            return new UsersExport();
        });

        return ($report)->download('users_' . $this->getDateTime() . '.csv');
    }

    public function readAllOrganisationsAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('organisationsReport', $duration, function () {
            return new OrganisationsExport();
        });

        return ($report)->download('organisations_' . $this->getDateTime() . '.csv');
    }

    public function readAllPitchesAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('pitchesReport', $duration, function () {
            return new PitchesExport();
        });

        return ($report)->download('pitches_' . $this->getDateTime() . '.csv');
    }

    public function readAllMonitoringsAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('monitoringsReport', $duration, function () {
            return new MonitoringsExport();
        });

        return ($report)->download('monitorings_' . $this->getDateTime() . '.csv');
    }

    public function readAllProgressUpdatesAction(Request $request): BinaryFileResponse
    {
        $this->authorize('readAll', 'App\\Models\\Report');
        $duration = now()->addMinutes(config('app.reports_cache_duration'));
        $report = Cache::remember('progressUpdatesReport', $duration, function () {
            return new ProgressUpdatesExport();
        });

        return ($report)->download('progress_updates_' . $this->getDateTime() . '.csv');
    }
}
