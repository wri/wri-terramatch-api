<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\DueSubmission;
use App\Models\Site;
use App\Resources\DueProgrammeSubmissionResource;
use App\Resources\DueSiteSubmissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DueSubmissionController extends Controller
{
    public function readAllDueSiteSubmissionsForUserAction(Request $request): JsonResponse
    {
        $this->authorize('readAllForUser', DueSubmission::class);

        $me = Auth::user();
        $siteIds = Site::whereIn('programme_id', $me->programmes->pluck('id'))->pluck('id');

        $dueSubmissions = DueSubmission::forSite()
            ->whereIn('due_submissionable_id', $siteIds)
            ->unsubmitted()
            ->orderByDesc('due_at')
            ->with('due_submissionable')
            ->get();

        $resources = [];
        foreach ($dueSubmissions as $dueSubmission) {
            $resources[] = new DueSiteSubmissionResource($dueSubmission);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllDueProgrammeSubmissionsForUserAction(Request $request): JsonResponse
    {
        $this->authorize('readAllForUser', DueSubmission::class);

        $me = Auth::user();

        $dueSubmissions = DueSubmission::forProgramme()
            ->whereIn('due_submissionable_id', $me->programmes->pluck('id'))
            ->unsubmitted()
            ->orderByDesc('due_at')
            ->with('due_submissionable')
            ->get();

        $resources = [];
        foreach ($dueSubmissions as $dueSubmission) {
            $resources[] = new DueProgrammeSubmissionResource($dueSubmission);
        }

        return JsonResponseHelper::success($resources, 200);
    }
}
