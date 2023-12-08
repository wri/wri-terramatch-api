<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\FilterTerrafundNurserySubmissionByDateRequest;
use App\Http\Requests\Terrafund\StoreTerrafundNurserySubmissionRequest;
use App\Http\Requests\Terrafund\UpdateTerrafundNurserySubmissionRequest;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Resources\Terrafund\TerrafundGenericSubmissionResource;
use App\Resources\Terrafund\TerrafundNurserySubmissionResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TerrafundNurserySubmissionController extends Controller
{
    public function createAction(StoreTerrafundNurserySubmissionRequest $request): JsonResponse
    {
        $data = $request->json()->all();
        $nursery = TerrafundNursery::findOrFail($data['terrafund_nursery_id']);
        $this->authorize('createSubmission', $nursery);

        $submission = TerrafundNurserySubmission::create($data);

        return JsonResponseHelper::success(new TerrafundNurserySubmissionResource($submission), 201);
    }

    public function readAction(TerrafundNurserySubmission $terrafundNurserySubmission)
    {
        $this->authorize('read', $terrafundNurserySubmission);

        return JsonResponseHelper::success(new TerrafundNurserySubmissionResource($terrafundNurserySubmission), 200);
    }

    public function updateAction(UpdateTerrafundNurserySubmissionRequest $request, TerrafundNurserySubmission $terrafundNurserySubmission): JsonResponse
    {
        $data = $request->json()->all();
        $this->authorize('update', $terrafundNurserySubmission);

        $terrafundNurserySubmission->update($data);

        return JsonResponseHelper::success(new TerrafundNurserySubmissionResource($terrafundNurserySubmission), 200);
    }

    public function filterByDateAction(FilterTerrafundNurserySubmissionByDateRequest $request)
    {
        $this->authorize('readAllForUser', TerrafundNurserySubmission::class);

        $data = $request->json()->all();

        $me = Auth::user();

        $nurseryIds = TerrafundNursery::whereIn('terrafund_programme_id', $me->terrafundProgrammes->pluck('id'))->pluck('id');

        $submissions = TerrafundNurserySubmission::submissionsBetween(Carbon::parse($data['start_date']), Carbon::parse($data['end_date']))
            ->whereIn('terrafund_nursery_id', $nurseryIds)
            ->orderByDesc('updated_at')
            ->get();
        $resources = $submissions->map(function (TerrafundNurserySubmission $submission) {
            return new TerrafundNurserySubmissionResource($submission);
        });

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllNurserySubmissions(Request $request, TerrafundNursery $terrafundNursery)
    {
        $this->authorize('read', $terrafundNursery);

        $submissions = $terrafundNursery->terrafundNurserySubmissions()->paginate(5);
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
