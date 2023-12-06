<?php

namespace App\Http\Controllers\V2\BaselineMonitoring;

use App\Helpers\V2Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\BaselineReporting\SiteMetricRequest;
use App\Http\Resources\V2\BaselineMonitoring\MetricsCollection;
use App\Http\Resources\V2\BaselineMonitoring\SiteMetricResource;
use App\Models\V2\BaselineMonitoring\ProjectMetric;
use App\Models\V2\BaselineMonitoring\SiteMetric;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BaselineMonitoringSiteController extends Controller
{

    public function index(): ResourceCollection
    {
        return new MetricsCollection(SiteMetric::paginate(config('app.pagination_default', 15)));
    }

    public function create(SiteMetricRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $model = V2Helper::getModel(
            data_get($validated, 'monitorable_type', ''),
            data_get($validated, 'monitorable_id', 0)
        );

        if(empty($model)){
            throw new ModelNotFoundException;
        }

        $validated['monitorable_type'] = get_class($model);
        $validated['monitorable_id'] = $model->id;
        $validated['status'] = ProjectMetric::STATUS_ACTIVE;
        $metric = SiteMetric::create($validated);

        return (new SiteMetricResource($metric))
            ->response()
            ->setStatusCode(201);
    }

    public function view($uuid): ?SiteMetricResource
    {
        if(empty($uuid)){
            return response()->json(['error' => 'no uuid provided'], 422);
        }

        $metric = SiteMetric::isUuid($uuid)
            ->first();

        if(empty($metric)){
            throw new ModelNotFoundException();
        }

        return new SiteMetricResource($metric);
    }

    public function update(SiteMetricRequest $request, string $uuid): JsonResponse
    {
        if(empty($uuid)){
            return response()->json(['error' => 'no uuid provided'], 422);
        }

        $validated = $request->validated();

        $metric = SiteMetric::isUuid($uuid)
            ->first();

        if(empty($metric)){
            throw new ModelNotFoundException();
        }

        $validated['status'] = SiteMetric::STATUS_ACTIVE;
        $metric->update($validated);

        return (new SiteMetricResource($metric))
            ->response()
            ->setStatusCode(201);
    }

    public function delete(string $uuid): JsonResponse
    {
        if(empty($uuid)){
            return response()->json(['error' => 'no uuid provided'], 422);
        }

        $metric = SiteMetric::isUuid($uuid)
            ->first();

        if(empty($metric)){
            throw new ModelNotFoundException();
        }

        $metric->delete();

        return response()->json(['success' => 'site metrics has been deleted'], 202);
    }
}
