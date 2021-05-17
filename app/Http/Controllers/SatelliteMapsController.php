<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Jobs\ConvertSatelliteMapJob;
use App\Jobs\NotifySatelliteMapCreatedJob;
use App\Models\SatelliteMap as SatelliteMapModel;
use App\Models\Monitoring as MonitoringModel;
use App\Resources\SatelliteMapResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Validators\SatelliteMapValidator;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\InvalidMonitoringException;

class SatelliteMapsController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\SatelliteMap");
        $data = $request->json()->all();
        SatelliteMapValidator::validate("CREATE", $data);
        $me = Auth::user();
        $data["map"] = UploadHelper::findByIdAndValidate(
            $data["map"], UploadHelper::MAPS, $me->id
        );
        $monitoring = MonitoringModel::findOrFail($data["monitoring_id"]);
        if ($monitoring->stage != "accepted_targets") {
            throw new InvalidMonitoringException();
        }
        $satelliteMap = new SatelliteMapModel($data);
        $satelliteMap->created_by = $me->id;
        $satelliteMap->saveOrFail();
        ConvertSatelliteMapJob::dispatch($satelliteMap);
        NotifySatelliteMapCreatedJob::dispatch($satelliteMap);
        $resource = new SatelliteMapResource($satelliteMap);
        return JsonResponseHelper::success($resource, 201);
    }

    public function readAllByMonitoringAction(Request $request, Int $id): JsonResponse
    {
        $monitoring = MonitoringModel::findOrFail($id);
        $this->authorize("read", $monitoring);
        $satelliteMaps = SatelliteMapModel
            ::where("monitoring_id", "=", $monitoring->id)
            ->orderBy("created_at", "asc")
            ->get();
        $this->authorize("readAll", "App\\Models\\SatelliteMap");
        $resources = [];
        foreach ($satelliteMaps as $satelliteMap) {
            $resources[] = new SatelliteMapResource($satelliteMap);
        }
        return JsonResponseHelper::success($resources, 200);
    }

    public function readLatestByMonitoringAction(Request $request, Int $id): JsonResponse
    {
        $monitoring = MonitoringModel::findOrFail($id);
        $satelliteMap = SatelliteMapModel
            ::where("monitoring_id", "=", $monitoring->id)
            ->orderBy("created_at", "desc")
            ->firstOrFail();
        $this->authorize("read", $satelliteMap);
        $resource = new SatelliteMapResource($satelliteMap);
        return JsonResponseHelper::success($resource, 200);
    }

    public function readAction(Request $request, Int $id): JsonResponse
    {
        $satelliteMap = SatelliteMapModel::findOrFail($id);
        $this->authorize("read", $satelliteMap);
        $resource = new SatelliteMapResource($satelliteMap);
        return JsonResponseHelper::success($resource, 200);
    }
}
