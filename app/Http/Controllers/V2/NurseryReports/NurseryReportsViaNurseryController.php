<?php

namespace App\Http\Controllers\V2\NurseryReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\NurseryReports\NurseryReportsCollection;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class NurseryReportsViaNurseryController extends Controller
{
    public function __invoke(Request $request, Nursery $nursery): NurseryReportsCollection
    {
        $this->authorize('read', $nursery);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'title', '-title',
            'due_at', '-due_at',
            'status', '-status',
        ];

        $qry = QueryBuilder::for(NurseryReport::class)
            ->allowedFilters([
                AllowedFilter::scope('project_uuid', 'projectUuid'),
                AllowedFilter::scope('country'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('framework_key'),
            ])
            ->where('nursery_id', $nursery->id)
            ->hasBeenSubmitted();

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if (! empty($request->query('search'))) {
            $ids = NurseryReport::search(trim($request->query('search')))->pluck('id')->toArray();
            $qry->whereIn('v2_nursery_reports.id', $ids);
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new NurseryReportsCollection($collection);
    }
}
