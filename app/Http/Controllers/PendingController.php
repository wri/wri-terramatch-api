<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\DueSubmission;
use App\Models\Programme;
use App\Models\Site;
use App\Models\SiteSubmission;
use App\Models\Submission;
use App\Resources\DueProgrammeSubmissionResource;
use App\Resources\DueSiteSubmissionResource;
use App\Resources\PendingResource;
use App\Resources\SiteSubmissionResource;
use App\Resources\SubmissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendingController extends Controller
{
    public function readPendingProgrammeSubmissionsAction(Request $request): JsonResponse
    {
        $this->authorize('pendingRead', Programme::class);
        $me = Auth::user();

        $programmeSubmissions = Submission::whereIn('programme_id', $me->programmes->pluck('id'))
            ->orderByDesc('created_at')
            ->get();
        $completeSubmissions = [];
        foreach ($programmeSubmissions as $programmeSubmission) {
            $completeSubmissions[] = new SubmissionResource($programmeSubmission);
        }

        $dueProgrammeSubmissions = DueSubmission::where('due_submissionable_type', Programme::class)
            ->whereIn('due_submissionable_id', $me->programmes->pluck('id'))
            ->where('is_submitted', 0)
            ->orderByDesc('due_at')
            ->with('due_submissionable')
            ->get();
        $dueSubmissions = [];
        foreach ($dueProgrammeSubmissions as $dueSubmission) {
            $dueSubmissions[] = new DueProgrammeSubmissionResource($dueSubmission);
        }

        $meta = [
            'total_reports' => count($completeSubmissions) + count($dueSubmissions),
            'outstanding_reports' => count($dueSubmissions),
        ];
        $resources = new PendingResource($completeSubmissions, $dueSubmissions);

        return JsonResponseHelper::success($resources, 200, (object)$meta);
    }

    public function readPendingSiteSubmissionsAction(Request $request): JsonResponse
    {
        $this->authorize('pendingRead', Site::class);
        $me = Auth::user();
        $siteIds = Site::whereIn('programme_id', $me->programmes->pluck('id'))->pluck('id');

        $siteSubmissions = SiteSubmission::whereIn('site_id', $siteIds)
            ->orderByDesc('created_at')
            ->get();
        $completeSubmissions = [];
        foreach ($siteSubmissions as $programmeSubmission) {
            $completeSubmissions[] = new SiteSubmissionResource($programmeSubmission);
        }

        $dueSiteSubmissions = DueSubmission::where('due_submissionable_type', Site::class)
            ->whereIn('due_submissionable_id', $siteIds)
            ->where('is_submitted', 0)
            ->orderByDesc('due_at')
            ->with('due_submissionable')
            ->get();
        $dueSubmissions = [];
        foreach ($dueSiteSubmissions as $dueSubmission) {
            $dueSubmissions[] = new DueSiteSubmissionResource($dueSubmission);
        }

        $meta = [
            'total_reports' => count($completeSubmissions) + count($dueSubmissions),
            'outstanding_reports' => count($dueSubmissions),
        ];
        $resources = new PendingResource($completeSubmissions, $dueSubmissions);

        return JsonResponseHelper::success($resources, 200, (object)$meta);
    }
}
