<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\UnableToReportRequest;
use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundSite;
use App\Resources\Terrafund\TerrafundDueSubmissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TerrafundDueSubmissionController extends Controller
{
    public function readAllDueSiteSubmissionsForUserAction(Request $request): JsonResponse
    {
        $this->authorize('readAllForUser', TerrafundDueSubmission::class);

        $me = Auth::user();
        $siteIds = TerrafundSite::whereIn('terrafund_programme_id', $me->terrafundProgrammes->pluck('id'))->pluck('id');


        $dueSubmissions = TerrafundDueSubmission::forTerrafundSite()
            ->whereIn('terrafund_due_submissionable_id', $siteIds)
            ->unsubmitted()
            ->whereNotNull('terrafund_due_submissionable_type')
            ->whereNotNull('terrafund_due_submissionable_id')
            ->orderByDesc('due_at')
            ->with('terrafund_due_submissionable')
            ->get();

        $resources = [];

        foreach ($dueSubmissions as $dueSubmission) {
            $resources[] = new TerrafundDueSubmissionResource($dueSubmission);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllDueNurserySubmissionsForUserAction(Request $request): JsonResponse
    {
        $this->authorize('readAllForUser', TerrafundDueSubmission::class);

        $me = Auth::user();

        $nurseryIds = TerrafundNursery::whereIn('terrafund_programme_id', $me->terrafundProgrammes->pluck('id'))->pluck('id');


        $dueSubmissions = TerrafundDueSubmission::forTerrafundNursery()
            ->whereIn('terrafund_due_submissionable_id', $nurseryIds)
            ->unsubmitted()
            ->orderByDesc('due_at')
            ->with('terrafund_due_submissionable')
            ->get();

        $resources = [];
        foreach ($dueSubmissions as $dueSubmission) {
            $resources[] = new TerrafundDueSubmissionResource($dueSubmission);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllPastSiteSubmissionsForUserAction(Request $request): JsonResponse
    {
        $this->authorize('readAllForUser', TerrafundDueSubmission::class);

        $me = Auth::user();
        $siteIds = TerrafundSite::whereIn('terrafund_programme_id', $me->terrafundProgrammes->pluck('id'))->pluck('id');


        $dueSubmissions = TerrafundDueSubmission::forTerrafundSite()
            ->whereIn('terrafund_due_submissionable_id', $siteIds)
            ->submitted()
            ->orderByDesc('due_at')
            ->with('terrafund_due_submissionable')
            ->get();

        $resources = [];
        foreach ($dueSubmissions as $dueSubmission) {
            $resources[] = new TerrafundDueSubmissionResource($dueSubmission);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllPastNurserySubmissionsForUserAction(Request $request): JsonResponse
    {
        $this->authorize('readAllForUser', TerrafundDueSubmission::class);

        $me = Auth::user();

        $nurseryIds = TerrafundNursery::whereIn('terrafund_programme_id', $me->terrafundProgrammes->pluck('id'))->pluck('id');


        $dueSubmissions = TerrafundDueSubmission::forTerrafundNursery()
            ->whereIn('terrafund_due_submissionable_id', $nurseryIds)
            ->submitted()
            ->orderByDesc('due_at')
            ->with('terrafund_due_submissionable')
            ->get();

        $resources = [];
        foreach ($dueSubmissions as $dueSubmission) {
            $resources[] = new TerrafundDueSubmissionResource($dueSubmission);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function unableToReportOnDueSubmissionAction(UnableToReportRequest $request, TerrafundDueSubmission $terrafundDueSubmission)
    {
        $this->authorize('updateDueSubmission', $terrafundDueSubmission);
        $data = $request->json()->all();

        TerrafundDueSubmission::query()
            ->unsubmitted()
            ->where('terrafund_programme_id', $terrafundDueSubmission->terrafund_programme_id)
            ->where('due_at', $terrafundDueSubmission->due_at) // when generated, time are set to be identical
            ->update([
                'unable_report_reason' => $data['reason'],
                'is_submitted' => true,
            ]);

        return JsonResponseHelper::success(new TerrafundDueSubmissionResource($terrafundDueSubmission->fresh()), 200);
    }
}
