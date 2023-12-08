<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\MatchHelper;
use App\Helpers\TaskHelper;
use App\Models\Monitoring as MonitoringModel;
use App\Models\OrganisationVersion as OrganisationVersionModel;
use App\Models\Pitch as PitchModel;
use App\Models\PitchVersion as PitchVersionModel;
use App\Resources\MatchResource;
use App\Resources\MonitoringResource;
use App\Resources\OrganisationLiteResource;
use App\Resources\PitchLiteResource;
use App\Resources\PitchResource;
use App\Services\Version\VersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TasksController extends Controller
{
    public function readAllOrganisationsAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', 'App\\Models\\Organisation');
        $me = Auth::user();

        $qry = OrganisationVersionModel::with('organisation')
            ->where('status', 'pending')
            ->orderBy('name', 'asc');

        if ($me->role == 'terrafund_admin') {
            $qry->where('account_type', 'terrafund');
        }

        $organisationVersions = $qry->get();

        $resources = [];
        foreach ($organisationVersions as $version) {
            $resources[] = new OrganisationLiteResource($version->organisation, $version);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    //    public function readAllPitchesAction(Request $request): JsonResponse
    //    {
    //        $this->authorize('readAll', 'App\\Models\\Task');
    //
    //        $pitches = TaskHelper::findPendingPitches();
    //        $versionService = new VersionService(new PitchModel(), new PitchVersionModel());
    //        $resources = [];
    //        foreach ($pitches as $pitch) {
    //            $parentAndChild = $versionService->groupAllChildren(function ($query) use ($pitch) {
    //                $query->where('id', '=', $pitch->pitch_id)
    //                    ->whereNotIn('visibility', ['archived', 'finished']);
    //            });
    //            if (count($parentAndChild) < 1) {
    //                continue;
    //            }
    //            $parentAndChild[0]->child->created_at = $pitch->max_created_at;
    //            $resources[] = new PitchResource($parentAndChild[0]->parent, $parentAndChild[0]->child);
    //        }
    //
    //        return JsonResponseHelper::success($resources, 200);
    //    }

    public function readAllPitchesAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', 'App\\Models\\Task');

        $pitches = PitchVersionModel::with('pitch')
            ->whereNotIn('status', ['archived', 'finished'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $resources = [];
        foreach ($pitches as $version) {
            $resources[] = new PitchLiteResource($version->pitch, $version);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllMatchesAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', 'App\\Models\\Task');
        $matches = MatchHelper::findMatches();
        $resources = [];
        foreach ($matches as $match) {
            $offerContacts = MatchHelper::findOfferContacts($match->offer_id);
            $pitchContacts = MatchHelper::findPitchContacts($match->pitch_id);
            $resources[] = new MatchResource($match, $offerContacts, $pitchContacts);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllMonitoringsAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', 'App\\Models\\Task');
        $monitorings =
            MonitoringModel::where('stage', '=', 'accepted_targets')
            ->with('matched.interest.pitch.approved_version')
            ->with('matched.interest.offer')
            ->get();
        $latestProgressUpdates =
            DB::table('progress_updates')
            ->select('monitoring_id')
            ->selectRaw('MAX(created_at) AS created_at')
            ->groupBy('monitoring_id')
            ->get();
        $latestProgressUpdatesCreatedAt = $latestProgressUpdates->pluck('created_at', 'monitoring_id')->toArray();
        $resources = [];
        foreach ($monitorings as $monitoring) {
            $latestProgressUpdateCreatedAt =
                array_key_exists($monitoring->id, $latestProgressUpdatesCreatedAt) ?
                Carbon::make($latestProgressUpdatesCreatedAt[$monitoring->id]) :
                null;
            $resources[] = new MonitoringResource($monitoring, $latestProgressUpdateCreatedAt);
        }

        return JsonResponseHelper::success($resources, 200);
    }
}
