<?php

namespace App\Http\Controllers\V2\FinancialReports;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\FinancialReports\FinancialReportResource;
use App\Http\Resources\V2\FinancialReports\FinancialReportsCollection;
use App\Models\V2\FinancialReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class FinancialReportsController extends Controller
{
    public function index(Request $request): FinancialReportsCollection
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'submitted_at', '-submitted_at',
            'year_of_report', '-year_of_report',
            'due_at', '-due_at',
            'status', '-status',
        ];

        $qry = QueryBuilder::for(FinancialReport::class)
            ->allowedFilters([
                AllowedFilter::scope('organisation_uuid', 'organisationUuid'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('year_of_report'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if ($request->query('search')) {
            $ids = FinancialIndicators::search(trim($request->query('search')))->pluck('id')->toArray();

            if (empty($ids)) {
                return new FinancialReportsCollection([]);
            }
            $qry->whereIn('id', $ids);
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new FinancialReportsCollection($collection);
    }

    public function show(FinancialReport $financialReport, Request $request): FinancialReportResource
    {
        $this->authorize('read', $financialReport);

        return new FinancialReportResource($financialReport);
    }

    public function update(FinancialReport $financialReport, Request $request): FinancialReportResource
    {
        $this->authorize('update', $financialReport);

        $financialReport->update($request->all());

        if ($request->get('tags')) {
            $financialReport->syncTags($request->get('tags'));
        }

        return new FinancialReportResource($financialReport);
    }

    public function destroy(FinancialReport $financialReport, Request $request): JsonResponse
    {
        $this->authorize('delete', $financialReport);

        $financialReport->delete();

        return JsonResponseHelper::success(['FinancialReport has been deleted.'], 200);
    }
}
