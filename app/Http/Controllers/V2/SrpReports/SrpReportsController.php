<?php

namespace App\Http\Controllers\V2\SrpReports;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SrpReports\SrpReportResource;
use App\Http\Resources\V2\SrpReports\SrpReportsCollection;
use App\Models\V2\SRPReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SrpReportsController extends Controller
{
    public function index(Request $request): SrpReportsCollection
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'submitted_at', '-submitted_at',
            'due_at', '-due_at',
            'status', '-status',
        ];

        $qry = QueryBuilder::for(SrpReport::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if ($request->query('search')) {
            $ids = SrpReport::search(trim($request->query('search')))->pluck('id')->toArray();

            if (empty($ids)) {
                return new SrpReportsCollection([]);
            }
            $qry->whereIn('id', $ids);
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new SrpReportsCollection($collection);
    }

    public function show(SrpReport $srpReport, Request $request): SrpReportResource
    {
        $this->authorize('read', $srpReport);

        return new SrpReportResource($srpReport);
    }

    public function update(SrpReport $srpReport, Request $request): SrpReportResource
    {
        $this->authorize('update', $srpReport);

        $srpReport->update($request->all());

        if ($request->get('tags')) {
            $srpReport->syncTags($request->get('tags'));
        }

        return new SrpReportResource($srpReport);
    }

    public function destroy(SrpReport $srpReport, Request $request): JsonResponse
    {
        $this->authorize('delete', $srpReport);

        $srpReport->delete();

        return JsonResponseHelper::success(['Annual Socio Economic Restoration Report has been deleted.'], 200);
    }
}
