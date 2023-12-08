<?php

namespace App\Http\Controllers;

use App\Exceptions\InvisibleOfferException;
use App\Exceptions\InvisiblePitchException;
use App\Helpers\JsonResponseHelper;
use App\Helpers\MatchHelper;
use App\Helpers\MonitoringHelper;
use App\Helpers\ProgressUpdateHelper;
use App\Http\Requests\StoreMonitoringsRequest;
use App\Models\Interest as InterestModel;
use App\Models\Matched as MatchModel;
use App\Models\Monitoring as MonitoringModel;
use App\Models\Offer as OfferModel;
use App\Models\Pitch as PitchModel;
use App\Models\ProgressUpdate as ProgressUpdateModel;
use App\Models\Target as TargetModel;
use App\Resources\MonitoringResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitoringsController extends Controller
{
    public function createAction(StoreMonitoringsRequest $request): JsonResponse
    {
        $data = $request->json()->all();
        $this->authorize('create', \App\Models\Monitoring::class);
        $match = MatchModel::findOrFail($data['match_id']);
        $this->authorize('read', $match);
        $interest = InterestModel::whereIn('id', [$match->primary_interest_id, $match->secondary_interest_id])
            ->where('organisation_id', '=', Auth::user()->organisation_id)
            ->firstOrFail();
        $initiatingAs = $interest->initiator;
        $offer = OfferModel::findOrFail($interest->offer_id);
        $pitch = PitchModel::findOrFail($interest->pitch_id);
        $validVisibilities = ['partially_invested_funded', 'fully_invested_funded'];
        $validOfferVisibility = in_array($offer->visibility, $validVisibilities);
        $validPitchVisibility = in_array($pitch->visibility, $validVisibilities);
        if ($initiatingAs == 'offer' && ! $validOfferVisibility) {
            throw new InvisibleOfferException();
        } elseif ($initiatingAs == 'pitch' && ! $validPitchVisibility) {
            throw new InvisiblePitchException();
        }

        $extra = [
            'initiator' => $initiatingAs,
            'negotiating' => $initiatingAs,
            'stage' => $validOfferVisibility && $validPitchVisibility ? 'negotiating_targets' : 'awaiting_visibilities',
            'created_by' => Auth::user()->id,
        ];

        $monitoring = MonitoringModel::create(array_merge($data, $extra));

        $resource = new MonitoringResource($monitoring);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(MonitoringModel $monitoring): JsonResponse
    {
        $this->authorize('read', $monitoring);
        $progressUpdate =
            DB::table('progress_updates')
                ->select('monitoring_id')
                ->selectRaw('MAX(created_at) AS created_at')
                ->where('monitoring_id', '=', $monitoring->id)
                ->groupBy('monitoring_id')
                ->first();
        $latestProgressUpdateCreatedAt =
            ! is_null($progressUpdate) ?
            Carbon::make($progressUpdate->created_at) :
            null;
        $resource = new MonitoringResource($monitoring, $latestProgressUpdateCreatedAt);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByOfferAction(OfferModel $offer): JsonResponse
    {
        $this->authorize('readAll', \App\Models\Monitoring::class);

        try {
            $this->authorize('update', $offer);
            $isOwner = true;
        } catch (AuthorizationException $exception) {
            $isOwner = false;
        }
        $monitoringIds = MonitoringHelper::findMonitoringIdsByOfferId($offer->id);
        $monitorings = MonitoringModel::with('matched.interest.pitch.approved_version')
            ->with('matched.interest.offer')
            ->whereIn('id', $monitoringIds)
            ->get();
        /**
         * This section removes monitorings which the current user doesn't own.
         * One offer may have multiple monitorings, each with a different pitch.
         * By checking the monitoring's match ID we can see if this monitoring
         * should be returned to the current user.
         */
        if (! $isOwner) {
            $matches = MatchHelper::findMatchesByOrganisation(Auth::user()->organisation_id);
            $matchIds = (new Collection($matches))->pluck('id')->toArray();
            foreach ($monitorings as $key => $monitoring) {
                if (! in_array($monitoring->match_id, $matchIds)) {
                    unset($monitorings[$key]);
                }
            }
        }
        $latestProgressUpdates =
            DB::table('progress_updates')
                ->select('monitoring_id')
                ->selectRaw('MAX(created_at) AS created_at')
                ->whereIn('id', $monitoringIds)
                ->groupBy('monitoring_id')
                ->get();
        $latestProgressUpdateCreatedAts = $latestProgressUpdates->pluck('created_at', 'monitoring_id')->toArray();
        $resources = [];
        foreach ($monitorings as $monitoring) {
            $latestProgressUpdateCreatedAt =
                array_key_exists($monitoring->id, $latestProgressUpdateCreatedAts) ?
                Carbon::make($latestProgressUpdateCreatedAts[$monitoring->id]) :
                null;
            $resources[] = new MonitoringResource($monitoring, $latestProgressUpdateCreatedAt);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllByPitchAction(PitchModel $pitch): JsonResponse
    {
        $this->authorize('readAll', \App\Models\Monitoring::class);

        try {
            $this->authorize('update', $pitch);
            $isOwner = true;
        } catch (AuthorizationException $exception) {
            $isOwner = false;
        }
        $monitoringIds = MonitoringHelper::findMonitoringIdsByPitchId($pitch->id);
        $monitorings = MonitoringModel::with('matched.interest.pitch.approved_version')
            ->with('matched.interest.offer')
            ->whereIn('id', $monitoringIds)
            ->get();
        /**
         * This section removes monitorings which the current user doesn't own.
         * One pitch may have multiple monitorings, each with a different offer.
         * By checking the monitoring's match ID we can see if this monitoring
         * should be returned to the current user.
         */
        if (! $isOwner) {
            $matches = MatchHelper::findMatchesByOrganisation(Auth::user()->organisation_id);
            $matchIds = (new Collection($matches))->pluck('id')->toArray();
            foreach ($monitorings as $key => $monitoring) {
                if (! in_array($monitoring->match_id, $matchIds)) {
                    unset($monitorings[$key]);
                }
            }
        }
        $latestProgressUpdates =
            DB::table('progress_updates')
                ->select('monitoring_id')
                ->selectRaw('MAX(created_at) AS created_at')
                ->whereIn('id', $monitoringIds)
                ->groupBy('monitoring_id')
                ->get();
        $latestProgressUpdateCreatedAts = $latestProgressUpdates->pluck('created_at', 'monitoring_id')->toArray();
        $resources = [];
        foreach ($monitorings as $monitoring) {
            $latestProgressUpdateCreatedAt =
                array_key_exists($monitoring->id, $latestProgressUpdateCreatedAts) ?
                Carbon::make($latestProgressUpdateCreatedAts[$monitoring->id]) :
                null;
            $resources[] = new MonitoringResource($monitoring, $latestProgressUpdateCreatedAt);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', \App\Models\Monitoring::class);
        $organisationId = Auth::user()->organisation_id;
        $rawMonitorings = MonitoringHelper::findMonitoringsByOrganisation($organisationId);
        $monitoringIds = (new Collection($rawMonitorings))->pluck('id')->toArray();
        $monitorings = MonitoringModel::with('matched.interest.pitch.approved_version')
            ->with('matched.interest.offer')
            ->whereIn('id', $monitoringIds)
            ->get();
        $latestProgressUpdates =
            DB::table('progress_updates')
                ->select('monitoring_id')
                ->selectRaw('MAX(created_at) AS created_at')
                ->whereIn('id', $monitoringIds)
                ->groupBy('monitoring_id')
                ->get();
        $latestProgressUpdateCreatedAts = $latestProgressUpdates->pluck('created_at', 'monitoring_id')->toArray();
        $resources = [];
        foreach ($monitorings as $monitoring) {
            $latestProgressUpdateCreatedAt =
                array_key_exists($monitoring->id, $latestProgressUpdateCreatedAts) ?
                Carbon::make($latestProgressUpdateCreatedAts[$monitoring->id]) :
                null;
            $resources[] = new MonitoringResource($monitoring, $latestProgressUpdateCreatedAt);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function summariseAction(Request $request, Int $id): JsonResponse
    {
        $monitoring = MonitoringModel::where('stage', '=', 'accepted_targets')
            ->where('id', '=', $id)
            ->firstOrFail();
        $this->authorize('summarise', $monitoring);
        $target = TargetModel::where('monitoring_id', '=', $monitoring->id)
            ->whereNotNull('accepted_at')
            ->firstOrFail();
        $progressUpdates = ProgressUpdateModel::where('monitoring_id', '=', $monitoring->id)
            ->orderBy('created_at')
            ->get();
        $data = [];
        foreach ($target->data as $attribute => $target) {
            $datum = (object) [
                'attribute' => $attribute,
                'target' => $target,
                'progress_update' => null,
                'updated_at' => null,
            ];
            foreach ($progressUpdates as $progressUpdate) {
                $datum = ProgressUpdateHelper::summarise($datum, $progressUpdate);
            }
            $data[] = $datum;
        }

        return JsonResponseHelper::success($data, 200);
    }

    public function readLandGeoJsonAction(Request $request, Int $id): Response
    {
        $monitoring = MonitoringModel::where('stage', '=', 'accepted_targets')
            ->where('id', '=', $id)
            ->firstOrFail();
        $this->authorize('readLandGeoJson', $monitoring);
        $target = TargetModel::where('monitoring_id', '=', $monitoring->id)
            ->whereNotNull('accepted_at')
            ->firstOrFail();
        $landGeoJson = json_encode(json_decode($target->land_geojson), JSON_PRETTY_PRINT);
        $filename = 'monitoring_' . $monitoring->id . '_land_geojson.geojson';
        $headers = [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return new Response($landGeoJson, 200, $headers);
    }
}
