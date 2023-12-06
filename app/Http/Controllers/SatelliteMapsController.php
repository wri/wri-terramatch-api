<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidMonitoringException;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreSatelliteMapRequest;
use App\Jobs\ConvertSatelliteMapJob;
use App\Jobs\NotifySatelliteMapCreatedJob;
use App\Models\Monitoring as MonitoringModel;
use App\Models\SatelliteMap as SatelliteMapModel;
use App\Resources\SatelliteMapResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SatelliteMapsController extends Controller
{
    public function createAction(StoreSatelliteMapRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\SatelliteMap::class);
        $data = $request->json()->all();
        $me = Auth::user();
        $data['map'] = UploadHelper::findByIdAndValidate(
            $data['map'],
            UploadHelper::MAPS,
            $me->id
        );
        $monitoring = MonitoringModel::findOrFail($data['monitoring_id']);
        if ($monitoring->stage != 'accepted_targets') {
            throw new InvalidMonitoringException();
        }

        $satelliteMap = SatelliteMapModel::create(array_merge($data, ['created_by' => $me->id]));

        ConvertSatelliteMapJob::dispatch($satelliteMap);
        NotifySatelliteMapCreatedJob::dispatch($satelliteMap);
        $resource = new SatelliteMapResource($satelliteMap);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAllByMonitoringAction(Request $request, Int $id): JsonResponse
    {
        $monitoring = MonitoringModel::findOrFail($id);
        $this->authorize('read', $monitoring);
        $satelliteMaps = SatelliteMapModel::where('monitoring_id', '=', $monitoring->id)
            ->orderBy('created_at')
            ->get();
        $this->authorize('readAll', \App\Models\SatelliteMap::class);
        $resources = [];
        foreach ($satelliteMaps as $satelliteMap) {
            $resources[] = new SatelliteMapResource($satelliteMap);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readLatestByMonitoringAction(Request $request, Int $id): JsonResponse
    {
        $monitoring = MonitoringModel::findOrFail($id);
        $satelliteMap = SatelliteMapModel::where('monitoring_id', '=', $monitoring->id)
            ->orderByDesc('created_at')
            ->firstOrFail();
        $this->authorize('read', $satelliteMap);
        $resource = new SatelliteMapResource($satelliteMap);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAction(Request $request, Int $id): JsonResponse
    {
        $satelliteMap = SatelliteMapModel::findOrFail($id);
        $this->authorize('read', $satelliteMap);
        $resource = new SatelliteMapResource($satelliteMap);

        return JsonResponseHelper::success($resource, 200);
    }
}
