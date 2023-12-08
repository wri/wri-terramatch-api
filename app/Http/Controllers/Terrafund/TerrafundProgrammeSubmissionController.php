<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\FilterTerrafundProgrammeSubmissionByDateRequest;
use App\Http\Requests\Terrafund\StoreTerrafundProgrammeSubmissionRequest;
use App\Http\Requests\Terrafund\UpdateTerrafundProgrammeSubmissionRequest;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Resources\Terrafund\TerrafundGenericSubmissionResource;
use App\Resources\Terrafund\TerrafundProgrammeSubmissionResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TerrafundProgrammeSubmissionController extends Controller
{
    public function createAction(StoreTerrafundProgrammeSubmissionRequest $request): JsonResponse
    {
        $data = $request->json()->all();
        $programme = TerrafundProgramme::findOrFail($data['terrafund_programme_id']);
        $this->authorize('createSubmission', $programme);

        if (data_get($data, 'challenges_faced')) {
            $data['challenges_and_lessons'] = $data['challenges_faced'];
        }
        unset($data['challenges_faced']);

        $submission = TerrafundProgrammeSubmission::create($data);

        return JsonResponseHelper::success(new TerrafundProgrammeSubmissionResource($submission), 201);
    }

    public function updateAction(UpdateTerrafundProgrammeSubmissionRequest $request, TerrafundProgrammeSubmission $terrafundProgrammeSubmission): JsonResponse
    {
        $data = $request->json()->all();
        $this->authorize('update', $terrafundProgrammeSubmission);

        if (data_get($data, 'challenges_faced')) {
            $data['challenges_and_lessons'] = $data['challenges_faced'];
        }
        unset($data['challenges_faced']);

        $terrafundProgrammeSubmission->update($data);

        return JsonResponseHelper::success(new TerrafundProgrammeSubmissionResource($terrafundProgrammeSubmission), 200);
    }

    public function readAction(TerrafundProgrammeSubmission $terrafundProgrammeSubmission): JsonResponse
    {
        $this->authorize('read', $terrafundProgrammeSubmission);

        return JsonResponseHelper::success(new TerrafundProgrammeSubmissionResource($terrafundProgrammeSubmission), 200);
    }

    public function filterByDateAction(FilterTerrafundProgrammeSubmissionByDateRequest $request)
    {
        $this->authorize('readAllForUser', TerrafundProgrammeSubmission::class);

        $data = $request->json()->all();

        $me = Auth::user();

        $programmeIds = TerrafundProgramme::whereIn('id', $me->terrafundProgrammes->pluck('id'))->pluck('id');

        $submissions = TerrafundProgrammeSubmission::submissionsBetween(Carbon::parse($data['start_date']), Carbon::parse($data['end_date']))
            ->whereIn('terrafund_programme_id', $programmeIds)
            ->orderByDesc('updated_at')
            ->get();
        $resources = $submissions->map(function (TerrafundProgrammeSubmission $submission) {
            return new TerrafundProgrammeSubmissionResource($submission);
        });

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllProgrammeSubmissions(Request $request, TerrafundProgramme $terrafundProgramme)
    {
        $this->authorize('read', $terrafundProgramme);

        $siteIds = $terrafundProgramme->terrafundSites()->pluck('id', 'id')->toArray();
        $sites = TerrafundSiteSubmission::select('id', 'created_at')
            ->selectRaw('? as submission_type', [TerrafundSiteSubmission::class])
            ->withAggregate('terrafundDueSubmission', 'due_at')
            ->whereIn('terrafund_site_id', $siteIds);

        $nurseryIds = $terrafundProgramme->terrafundNurseries()->pluck('id', 'id')->toArray();
        $nurseries = TerrafundNurserySubmission::select('id', 'created_at')
            ->selectRaw('? as submission_type', [TerrafundNurserySubmission::class])
            ->withAggregate('terrafundDueSubmission', 'due_at')
            ->whereIn('terrafund_nursery_id', $nurseryIds);

        $submissions = TerrafundProgrammeSubmission::select('id', 'created_at')
            ->selectRaw('? as submission_type', [TerrafundProgrammeSubmission::class])
            ->withAggregate('terrafundDueSubmission', 'due_at')
            ->where('terrafund_programme_id', $terrafundProgramme->id)
            ->union($sites)
            ->union($nurseries)
            ->orderBy('terrafund_due_submission_due_at', 'desc')
            ->paginate(5);

        $resources = [];
        foreach ($submissions as $item) {
            $submission = $item->submission_type::find($item->id);
            $resources[] = new TerrafundGenericSubmissionResource($submission);
        }

        $meta = (object)[
            'first' => $submissions->firstItem(),
            'current' => $submissions->currentPage(),
            'last' => $submissions->lastPage(),
            'total' => $submissions->total(),
        ];

        return JsonResponseHelper::success($resources, 200, $meta);
    }
}
