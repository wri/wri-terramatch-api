<?php

namespace App\Http\Controllers\V2\BaselineMonitoring;

use App\Helpers\V2Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\BaselineReporting\ProjectMetricRequest;
use App\Http\Requests\V2\File\UploadRequest;
use App\Http\Resources\V2\BaselineMonitoring\MetricsCollection;
use App\Http\Resources\V2\BaselineMonitoring\ProjectMetricResource;
use App\Models\V2\BaselineMonitoring\ProjectMetric;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Support\MediaStream;

class BaselineMonitoringProjectController extends Controller
{
    public function index(): ResourceCollection
    {
        return new MetricsCollection(ProjectMetric::paginate(config('app.pagination_default', 15)));
    }

    public function create(ProjectMetricRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $this->authorize('create', ProjectMetric::class);


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
        $metric = ProjectMetric::create($validated);

        return (new ProjectMetricResource($metric))
            ->response()
            ->setStatusCode(201);
    }

    public function view(string $uuid): JsonResponse
    {

        if(empty($uuid)){
            return response()->json(['error' => 'no uuid provided'], 422);
        }

        $metric = ProjectMetric::isUuid($uuid)
            ->first();

        if(empty($metric)){
            throw new ModelNotFoundException();
        }

        $this->authorize('view', $metric);

        return (new ProjectMetricResource($metric))
            ->response()
            ->setStatusCode(200);
    }

    public function update(ProjectMetricRequest $request, string $uuid): JsonResponse
    {
        if(empty($uuid)){
            return response()->json(['error' => 'no uuid provided'], 422);
        }

        $validated = $request->validated();

        $metric = ProjectMetric::isUuid($uuid)
            ->first();

        if(empty($metric)){
            throw new ModelNotFoundException();
        }

        $this->authorize('update', $metric);

        $validated['status'] = ProjectMetric::STATUS_ACTIVE;
        $metric->update($validated);

        return (new ProjectMetricResource($metric->fresh()))
            ->response()
            ->setStatusCode(200);
    }

    public function delete(string $uuid): JsonResponse
    {
        if(empty($uuid)){
            return response()->json(['error' => 'no uuid provided'], 422);
        }

        $metric = ProjectMetric::isUuid($uuid)
            ->first();

        if(empty($metric)){
            throw new ModelNotFoundException();
        }

        $this->authorize('delete', $metric);

        $metric->delete();

        return response()->json(['success' => 'project metrics has been deleted'], 202);
    }

    public function upload(UploadRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $this->authorize('upload', ProjectMetric::class);

        $uuid = data_get($validated, 'uuid', null);
        $collectionName = data_get($validated,'collection', 'general');

        if( $uuid ) {
            $metric = ProjectMetric::isUuid(data_get($validated, 'uuid'))
                ->first();
        }

        if (empty($metric)) {
            $metric = ProjectMetric::create([
                'status' => ProjectMetric::STATUS_TEMP,
                'uuid' => $uuid ?? Str::uuid()->toString()
                ]);
        }

        if(in_array($collectionName, ['cover', 'reportPDF'])){
            $metric->clearMediaCollection($collectionName);
        }

        if(data_get($validated, 'title', false)){
            $metric->addMediaFromRequest('upload_file')
                ->addCustomHeaders([
                    'ACL' => 'public-read'
                ])
                ->usingName(data_get($validated, 'title'))
                ->toMediaCollection($collectionName);
        } else {
            $metric->addMediaFromRequest('upload_file')
                ->addCustomHeaders([
                    'ACL' => 'public-read'
                ])
                ->toMediaCollection($collectionName);
        }

        return (new ProjectMetricResource($metric->fresh()))
            ->response()
            ->setStatusCode(201);
    }

    public function overview(string $uuid): JsonResponse
    {
        if(empty($uuid)){
            return response()->json(['error' => 'no uuid provided'], 422);
        }

        $metric = ProjectMetric::isUuid($uuid)
            ->first();

        if(empty($metric)){
            throw new ModelNotFoundException();
        }

        $this->authorize('overview', $metric);
        return (new ProjectMetricResource($metric))
            ->response()
            ->setStatusCode(200);
    }

    public function download(string $uuid): MediaStream
    {
        if(empty($uuid)){
            return response()->json(['error' => 'no uuid provided'], 422);
        }

        $metric = ProjectMetric::isUuid($uuid)
            ->first();

        if(empty($metric)){
            throw new ModelNotFoundException();
        }

        $this->authorize('download', $metric);
        $supportFiles = $metric->getMedia('support');
        $reportFiles = $metric->getMedia('reportPDF');

        return MediaStream::create('Baseline Monitoring Files.zip')
            ->addMedia($supportFiles)
            ->addMedia($reportFiles);
    }

}
