<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\FilterTerrafundSiteSubmissionByDateRequest;
use App\Http\Requests\Terrafund\StoreTerrafundSiteSubmissionRequest;
use App\Http\Requests\Terrafund\UpdateTerrafundSiteSubmissionRequest;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Resources\Terrafund\TerrafundGenericSubmissionResource;
use App\Resources\Terrafund\TerrafundSiteSubmissionResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TerrafundSiteSubmissionController extends Controller
{
    public function createAction(StoreTerrafundSiteSubmissionRequest $request): JsonResponse
    {
        $data = $request->json()->all();
        $site = TerrafundSite::findOrFail($data['terrafund_site_id']);
        $this->authorize('createSubmission', $site);

        $submission = TerrafundSiteSubmission::create($data);

        return JsonResponseHelper::success(new TerrafundSiteSubmissionResource($submission), 201);
    }

    public function readAction(TerrafundSiteSubmission $terrafundSiteSubmission)
    {
        $this->authorize('read', $terrafundSiteSubmission);

        return JsonResponseHelper::success(new TerrafundSiteSubmissionResource($terrafundSiteSubmission), 200);
    }

    public function updateAction(UpdateTerrafundSiteSubmissionRequest $request, TerrafundSiteSubmission $terrafundSiteSubmission): JsonResponse
    {
        $data = $request->json()->all();
        $this->authorize('update', $terrafundSiteSubmission);

        $terrafundSiteSubmission->update($data);

        return JsonResponseHelper::success(new TerrafundSiteSubmissionResource($terrafundSiteSubmission), 200);
    }

    public function filterByDateAction(FilterTerrafundSiteSubmissionByDateRequest $request)
    {
        $this->authorize('readAllForUser', TerrafundSiteSubmission::class);

        $data = $request->json()->all();

        $me = Auth::user();

        $siteIds = TerrafundSite::whereIn('terrafund_programme_id', $me->terrafundProgrammes->pluck('id'))->pluck('id');

        $submissions = TerrafundSiteSubmission::submissionsBetween(Carbon::parse($data['start_date']), Carbon::parse($data['end_date']))
                        ->whereIn('terrafund_site_id', $siteIds)
                        ->orderByDesc('updated_at')
                        ->get();
        $resources = $submissions->map(function (TerrafundSiteSubmission $submission) {
            return new TerrafundSiteSubmissionResource($submission);
        });

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllSiteSubmissions(Request $request, TerrafundSite $terrafundSite)
    {
        $this->authorize('read', $terrafundSite);

        $submissions = $terrafundSite->terrafundSiteSubmissions()->paginate(5);
        $resources = [];
        foreach ($submissions as $submission) {
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
