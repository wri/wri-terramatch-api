<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\SatelliteMonitorHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreSatelliteMonitorRequest;
use App\Jobs\ConvertSatelliteMonitorJob;
use App\Models\Programme;
use App\Models\SatelliteMonitor;
use App\Models\Site;
use App\Resources\SatelliteMonitorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SatelliteMonitorController extends Controller
{
    public function createAction(StoreSatelliteMonitorRequest $request): JsonResponse
    {
        $this->authorize('create', SatelliteMonitor::class);
        $data = $request->json()->all();

        $data['satellite_monitorable_type'] = SatelliteMonitorHelper::translateModel($data['satellite_monitorable_type']);
        $me = Auth::user();
        $data['map'] = UploadHelper::findByIdAndValidate(
            $data['map'],
            UploadHelper::MAPS,
            $me->id
        );

        $satelliteMonitor = SatelliteMonitor::create($data);


        ConvertSatelliteMonitorJob::dispatch($satelliteMonitor);

        return JsonResponseHelper::success(new SatelliteMonitorResource($satelliteMonitor), 201);
    }

    public function readAllByProgramme(Programme $programme): JsonResponse
    {
        $this->authorize('read', SatelliteMonitor::class);
        $this->authorize('read', $programme);

        $satelliteMonitors = SatelliteMonitor::where('satellite_monitorable_type', \App\Models\Programme::class)
            ->where('satellite_monitorable_id', $programme->id)
            ->get();

        $resources = [];
        foreach ($satelliteMonitors as $satelliteMonitor) {
            $resources[] = new SatelliteMonitorResource($satelliteMonitor);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readLatestByProgramme(Programme $programme): JsonResponse
    {
        $this->authorize('read', SatelliteMonitor::class);
        $this->authorize('read', $programme);

        $satelliteMonitor = SatelliteMonitor::where('satellite_monitorable_type', \App\Models\Programme::class)
            ->where('satellite_monitorable_id', $programme->id)
            ->orderByDesc('created_at')
            ->firstOrFail();

        return JsonResponseHelper::success(new SatelliteMonitorResource($satelliteMonitor), 200);
    }

    public function readAllBySite(Site $site): JsonResponse
    {
        $this->authorize('read', SatelliteMonitor::class);
        $this->authorize('read', $site);

        $satelliteMonitors = SatelliteMonitor::where('satellite_monitorable_type', \App\Models\Site::class)
            ->where('satellite_monitorable_id', $site->id)
            ->get();

        $resources = [];
        foreach ($satelliteMonitors as $satelliteMonitor) {
            $resources[] = new SatelliteMonitorResource($satelliteMonitor);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readLatestBySite(Site $site): JsonResponse
    {
        $this->authorize('read', SatelliteMonitor::class);
        $this->authorize('read', $site);

        $satelliteMonitor = SatelliteMonitor::where('satellite_monitorable_type', \App\Models\Site::class)
            ->where('satellite_monitorable_id', $site->id)
            ->orderByDesc('created_at')
            ->firstOrFail();

        return JsonResponseHelper::success(new SatelliteMonitorResource($satelliteMonitor), 200);
    }
}
