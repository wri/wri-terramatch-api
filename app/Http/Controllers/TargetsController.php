<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidMonitoringException;
use App\Exceptions\InvalidNegotiatorException;
use App\Exceptions\InvalidTargetException;
use App\Exceptions\OldTargetException;
use App\Helpers\ErrorHelper;
use App\Helpers\JsonResponseHelper;
use App\Jobs\NotifyTargetAcceptedJob;
use App\Jobs\NotifyTargetCreatedJob;
use App\Jobs\NotifyTargetUpdatedJob;
use App\Models\Interest as InterestModel;
use App\Models\Matched as MatchModel;
use App\Models\Monitoring as MonitoringModel;
use App\Models\Target as TargetModel;
use App\Resources\TargetResource;
use App\Validators\TargetValidator;
use DateTime;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TargetsController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $data = $request->json()->all();
        TargetValidator::validate('CREATE', $data);

        try {
            TargetValidator::validate('CREATE_DATA', $data['data']);
        } catch (ValidationException $exception) {
            $errors = ErrorHelper::prefix($exception->errors(), 'data.');

            return JsonResponseHelper::error($errors, 422);
        }
        $this->authorize('create', \App\Models\Target::class);
        $monitoring = MonitoringModel::findOrFail($data['monitoring_id']);
        $this->authorize('read', $monitoring);
        if ($monitoring->stage == 'accepted_targets') {
            throw new InvalidMonitoringException();
        }
        $match = MatchModel::findOrFail($monitoring->match_id);
        $interest = InterestModel::whereIn('id', [$match->primary_interest_id, $match->secondary_interest_id])
            ->where('organisation_id', '=', Auth::user()->organisation_id)
            ->firstOrFail();
        $negotiatingAs = $interest->initiator;
        if ($negotiatingAs != $monitoring->negotiating) {
            throw new InvalidNegotiatorException();
        }
        $targets = TargetModel::where('monitoring_id', '=', $monitoring->id)->count();
        if ($monitoring->stage == 'awaiting_visibilities' && $targets > 0) {
            $exception = 'Invisible' . ucfirst($negotiatingAs) . 'Exception';

            throw new $exception();
        }
        $target = new TargetModel($data);
        $target->negotiator = $negotiatingAs;
        $target->created_by = Auth::user()->id;
        $target->saveOrFail();
        if (! $targets) {
            NotifyTargetCreatedJob::dispatch($target, ucfirst($target->unnegotiator));
        } else {
            NotifyTargetUpdatedJob::dispatch($target, ucfirst($target->unnegotiator));
        }
        $monitoring->negotiating = $target->unnegotiator;
        $monitoring->saveOrFail();
        $resource = new TargetResource($target);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(Request $request, Int $id): JsonResponse
    {
        $target = TargetModel::findOrFail($id);
        $this->authorize('read', $target);
        $resource = new TargetResource($target);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByMonitoringAction(Request $request, Int $id): JsonResponse
    {
        $targets = TargetModel::where('monitoring_id', '=', $id)->orderBy('created_at')->get();
        $this->authorize('readAll', \App\Models\Target::class);
        $monitoring = MonitoringModel::findOrFail($id);
        $this->authorize('read', $monitoring);
        $resources = [];
        foreach ($targets as $target) {
            $resources[] = new TargetResource($target);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAcceptedByMonitoringAction(Request $request, Int $id): JsonResponse
    {
        $target = TargetModel::where('monitoring_id', '=', $id)->whereNotNull('accepted_at')->firstOrFail();
        $this->authorize('read', $target);
        $resource = new TargetResource($target);

        return JsonResponseHelper::success($resource, 200);
    }

    public function acceptAction(Request $request, Int $id): JsonResponse
    {
        $target = TargetModel::findOrFail($id);
        $this->authorize('update', $target);
        if (is_null($target->land_geojson)) {
            throw new InvalidTargetException();
        }
        $latestTarget = TargetModel::where('monitoring_id', '=', $target->monitoring_id)
            ->orderByDesc('created_at')
            ->first();
        if ($target->id != $latestTarget->id) {
            throw new OldTargetException();
        }
        $monitoring = MonitoringModel::findOrFail($target->monitoring_id);
        $match = MatchModel::findOrFail($monitoring->match_id);
        $interest = InterestModel::whereIn('id', [$match->primary_interest_id, $match->secondary_interest_id])
            ->where('organisation_id', '=', Auth::user()->organisation_id)
            ->firstOrFail();
        $negotiatingAs = $interest->initiator;
        if ($negotiatingAs != $monitoring->negotiating) {
            throw new InvalidNegotiatorException();
        }
        $monitoring->negotiating = null;
        $monitoring->stage = 'accepted_targets';
        $monitoring->saveOrFail();
        $target->accepted_at = new DateTime('now', new DateTimeZone('UTC'));
        $target->accepted_by = Auth::user()->id;
        $target->saveOrFail();
        NotifyTargetAcceptedJob::dispatch($target);
        $resource = new TargetResource($target);

        return JsonResponseHelper::success($resource, 200);
    }
}
