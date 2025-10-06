<?php

namespace App\Http\Controllers\V2\DisturbanceReports;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\DisturbanceReports\DisturbanceReportResource;
use App\Http\Resources\V2\DisturbanceReports\DisturbanceReportsCollection;
use App\Models\V2\DisturbanceReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DisturbanceReportsController extends Controller
{
    public function index(Request $request): DisturbanceReportsCollection
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'submitted_at', '-submitted_at',
            'due_at', '-due_at',
            'status', '-status',
        ];

        $qry = QueryBuilder::for(DisturbanceReport::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if ($request->query('search')) {
            $ids = DisturbanceReport::search(trim($request->query('search')))->pluck('id')->toArray();

            if (empty($ids)) {
                return new DisturbanceReportsCollection([]);
            }
            $qry->whereIn('id', $ids);
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new DisturbanceReportsCollection($collection);
    }

    public function show(DisturbanceReport $disturbanceReport, Request $request): DisturbanceReportResource
    {
        $this->authorize('read', $disturbanceReport);

        return new DisturbanceReportResource($disturbanceReport);
    }

    public function update(DisturbanceReport $disturbanceReport, Request $request): DisturbanceReportResource
    {
        $this->authorize('update', $disturbanceReport);

        $disturbanceReport->update($request->all());

        if ($request->get('tags')) {
            $disturbanceReport->syncTags($request->get('tags'));
        }

        return new DisturbanceReportResource($disturbanceReport);
    }

    public function destroy(DisturbanceReport $disturbanceReport, Request $request): JsonResponse
    {
        $this->authorize('delete', $disturbanceReport);

        $disturbanceReport->delete();

        return JsonResponseHelper::success(['DisturbanceReport has been deleted.'], 200);
    }
}
